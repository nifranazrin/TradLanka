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
import threading
from create_text_index import generate_text_index
from create_index import generate_visual_index

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
            # If the similarity is less than 15%, STOP. Do not show random items.
            if score < 0.15: 
                break 
                
            pid = product_ids[i]

            recommendations.append(pid)
            if len(recommendations) >= 7: 
                break
                
        return jsonify(recommendations)
    except Exception as e:
        return jsonify({"error": str(e)}), 500
    
# --- ENDPOINT 4: RETRAIN & RELOAD ---
@app.route('/retrain', methods=['POST'])
def retrain_system():
    def background_task():
        print("\n[AI] --- RETRAINING STARTED ---")
        try:
            # 1. Run Text Training (Fast)
            generate_text_index()
            
            # 2. Run Visual Training (Slow) - Optional, can comment out if too slow
            generate_visual_index()
            
            # 3. Reload System Memory
            load_system()
            print("[AI] --- RETRAINING COMPLETE & RELOADED ---\n")
        except Exception as e:
            print(f"[AI] Retrain Error: {e}")

    # Run in background thread so PHP doesn't wait/timeout
    thread = threading.Thread(target=background_task)
    thread.start()

    return jsonify({"status": "success", "message": "Retraining started in background."})
    
# --- NEW ENDPOINT: HOT RELOAD ---
@app.route('/reload', methods=['POST'])
def reload_server():
    try:
        print("\n [AI] Reload signal received. Refreshing system...")
        load_system() # This function re-reads the .pkl files from disk
        return jsonify({"status": "success", "message": "AI System Reloaded Successfully"}), 200
    except Exception as e:
        print(f" [AI] Reload Failed: {e}")
        return jsonify({"error": str(e)}), 500


if __name__ == '__main__':
    # Running in debug mode
    app.run(host='127.0.0.1', port=5000, debug=True)