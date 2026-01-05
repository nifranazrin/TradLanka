from flask import Flask, request, jsonify
import numpy as np
import pickle
import os
import io
import pandas as pd

# AI & Math Imports
from tensorflow.keras.applications.mobilenet_v2 import MobileNetV2, preprocess_input
from tensorflow.keras.preprocessing import image
from tensorflow.keras.layers import GlobalAveragePooling2D
from tensorflow.keras.models import Sequential
from sklearn.neighbors import NearestNeighbors
from sklearn.metrics.pairwise import linear_kernel, cosine_similarity

# Database Imports (Optional - wrapped to prevent crash)
try:
    from sqlalchemy import create_engine
    HAS_DB = True
except ImportError:
    HAS_DB = False

app = Flask(__name__)

# --- CONFIGURATION ---
IMG_WIDTH, IMG_HEIGHT = 224, 224
BASE_PATH = os.path.dirname(os.path.abspath(__file__))

# File Paths
VISUAL_INDEX = os.path.join(BASE_PATH, 'features.pkl')
VISUAL_NAMES = os.path.join(BASE_PATH, 'filenames.pkl')
TEXT_INDEX = os.path.join(BASE_PATH, 'text_features.pkl')
TEXT_IDS = os.path.join(BASE_PATH, 'product_ids.pkl')

# DB CONFIG (Update user/pass if needed)
DB_CONNECTION_STR = 'mysql+mysqlconnector://root:@127.0.0.1/tradlanka_db'

# --- GLOBAL VARIABLES ---
model = None
feature_list = None
filenames = None
neighbors = None
tfidf_matrix = None
product_ids = None
db_engine = None

# --- INITIALIZATION ---
def load_system():
    global model, feature_list, filenames, neighbors, tfidf_matrix, product_ids, db_engine

    print(" [AI] Initializing System...")

    # 1. LOAD VISUAL SEARCH
    if os.path.exists(VISUAL_INDEX) and os.path.exists(VISUAL_NAMES):
        try:
            feature_list = pickle.load(open(VISUAL_INDEX, 'rb'))
            filenames = pickle.load(open(VISUAL_NAMES, 'rb'))
            
            # Load Keras Model
            base_model = MobileNetV2(weights='imagenet', include_top=False, input_shape=(IMG_WIDTH, IMG_HEIGHT, 3))
            base_model.trainable = False
            model = Sequential([base_model, GlobalAveragePooling2D()])
            
            # Init Neighbors
             # Change n_neighbors from 10 to 50 for better filtering options
            neighbors = NearestNeighbors(n_neighbors=50, algorithm='brute', metric='euclidean')
            neighbors.fit(feature_list)
            print(" [AI] Visual Search Ready.")
        except Exception as e:
            print(f" [AI] Visual Load Error: {e}")
    else:
        print(" [AI] Visual Index missing. (Run create_index.py)")

    # 2. LOAD TEXT RECOMMENDATIONS
    if os.path.exists(TEXT_INDEX) and os.path.exists(TEXT_IDS):
        try:
            tfidf_matrix = pickle.load(open(TEXT_INDEX, 'rb'))
            product_ids = pickle.load(open(TEXT_IDS, 'rb'))
            print(" [AI] Text Recommendation Ready.")
        except Exception as e:
            print(f" [AI] Text Load Error: {e}")
    else:
        print(" [AI] Text Index missing. (Run create_text_index.py)")

    # 3. CONNECT DATABASE (Optional)
    if HAS_DB:
        try:
            db_engine = create_engine(DB_CONNECTION_STR)
            print(" [AI] Database Connected.")
        except Exception as e:
            print(f" [AI] Database Warning: {e}")
            db_engine = None
    else:
        print(" [AI] Database driver missing (mysql-connector-python). Hybrid logic disabled.")

# Load everything on startup
load_system()

# --- ENDPOINT 1: VISUAL SEARCH (Image Upload) ---
@app.route('/search', methods=['POST'])
def search():
    if model is None: 
        return jsonify({"error": "Visual AI not loaded"}), 500
    
    if 'image' not in request.files:
        return jsonify({"error": "No image uploaded"}), 400

    try:
        file = request.files['image']
        img_bytes = io.BytesIO(file.read())
        img = image.load_img(img_bytes, target_size=(IMG_WIDTH, IMG_HEIGHT))
        img_array = image.img_to_array(img)
        expanded_img_array = np.expand_dims(img_array, axis=0)
        preprocessed_img = preprocess_input(expanded_img_array)
        
        query_features = model.predict(preprocessed_img, verbose=0).flatten()
        query_features = query_features / np.linalg.norm(query_features)
        
        # kneighbors returns (distances, indices)
        distances, indices = neighbors.kneighbors([query_features])
        
        results = []
        for i in range(len(indices[0])):
            idx = indices[0][i]
            dist = float(distances[0][i]) # Capture the raw Euclidean distance
            
            # Use distance to calculate similarity
            # Lower distance means higher similarity
            similarity = max(0, 1 - (dist / 1.4))
            
            results.append({
                "filename": filenames[idx], 
                "similarity": similarity,
                "distance": dist # Include raw distance in result
            })
            
        return jsonify(results)
    except Exception as e:
        return jsonify({"error": str(e)}), 500

# --- ENDPOINT 2: HYBRID RECOMMENDATION (Pandas + Visual Fallback) ---
@app.route('/recommend', methods=['POST'])
def recommend():
    # Expects JSON: { "product_ids": [10, 20], "filenames": ["img1.jpg", "img2.jpg"] }
    data = request.json
    req_pids = data.get('product_ids', [])
    req_files = data.get('filenames', [])
    recommendations = []

    print(f"\n[REQ] Processing Hybrid recommendation for IDs: {req_pids}")

    # STRATEGY A: Try Pandas (User Behavior) FIRST
    if db_engine and req_pids:
        try:
            # Fetch History Data
            query = "SELECT user_id, product_id, count(*) as view_count FROM product_views GROUP BY user_id, product_id"
            df = pd.read_sql(query, db_engine)
            
            if len(df) > 5: # Only run if we have enough data
                user_item_matrix = df.pivot_table(index='user_id', columns='product_id', values='view_count').fillna(0)
                item_similarity = cosine_similarity(user_item_matrix.T)
                sim_df = pd.DataFrame(item_similarity, index=user_item_matrix.columns, columns=user_item_matrix.columns)
                
                # Find similar items to the last viewed product
                last_id = req_pids[0]
                
                if last_id in sim_df.index:
                    # Get top 5 similar products (excluding itself)
                    similar_products = sim_df[last_id].sort_values(ascending=False).iloc[1:6]
                    print(f"[PANDAS] Found behavioral matches: {similar_products.index.tolist()}")
                    
                    # Note: The logic to convert these IDs back to filenames or models happens in Laravel
                    # We could return them here, but for now we fall through to Visual if this list is empty
                    # or if you want to mix them.
        except Exception as e:
            print(f"[PANDAS] Skipped (Error): {e}")

    # STRATEGY B: Visual AI (The Fallback)
    if not recommendations and req_files and filenames:
        print("[VISUAL] Using Visual AI fallback...")
        target_indices = []
        for fname in req_files:
            if fname in filenames:
                target_indices.append(filenames.index(fname))
        
        if target_indices:
            selected_features = [feature_list[i] for i in target_indices]
            user_vector = np.mean(selected_features, axis=0)
            user_vector = user_vector / np.linalg.norm(user_vector)
            
            distances, indices = neighbors.kneighbors([user_vector], n_neighbors=10)
            
            for i in range(len(indices[0])):
                idx = indices[0][i]
                if filenames[idx] not in req_files:
                    recommendations.append({"filename": filenames[idx]})

    return jsonify(recommendations)


# --- ENDPOINT 3: TEXT RECOMMENDATION (Product History IDs) ---
# This is what your new FrontendController uses!
@app.route('/recommend-text', methods=['POST'])
def recommend_text():
    if tfidf_matrix is None:
        return jsonify([])
    
    try:
        data = request.json
        history_ids = data.get('history_ids', [])
        
        # Find indices of viewed products
        indices = [product_ids.index(pid) for pid in history_ids if pid in product_ids]
        
        if not indices:
            return jsonify([])

        # Average vector of user history
        user_profile = np.asarray(tfidf_matrix[indices].mean(axis=0))

        # Calculate similarity
        cosine_sim = linear_kernel(user_profile, tfidf_matrix)

        # Get top results
        sim_scores = list(enumerate(cosine_sim[0]))
        sim_scores = sorted(sim_scores, key=lambda x: x[1], reverse=True)
        
        recommendations = []
        for i, score in sim_scores:
            pid = product_ids[i]
            if pid not in history_ids: # Exclude seen
                recommendations.append(pid)
                if len(recommendations) >= 10: break
                
        return jsonify(recommendations)
    except Exception as e:
        return jsonify({"error": str(e)}), 500


if __name__ == '__main__':
    # Running in debug mode
    app.run(host='127.0.0.1', port=5000, debug=True)