
import uvicorn
from fastapi import FastAPI, File, UploadFile, HTTPException
from pydantic import BaseModel
import numpy as np
import pickle
import os
import io
import pandas as pd
from sqlalchemy import create_engine
from sklearn.neighbors import NearestNeighbors
from sklearn.metrics.pairwise import cosine_similarity
from tensorflow.keras.applications.mobilenet_v2 import MobileNetV2, preprocess_input
from tensorflow.keras.preprocessing import image
from tensorflow.keras.layers import GlobalMaxPooling2D
from tensorflow.keras.models import Sequential

# --- APP SETUP ---
app = FastAPI()

# --- CONFIGURATION ---
IMG_WIDTH, IMG_HEIGHT = 224, 224
BASE_PATH = os.path.dirname(os.path.abspath(__file__))
INDEX_FILE = os.path.join(BASE_PATH, 'features.pkl')
FILENAMES_FILE = os.path.join(BASE_PATH, 'filenames.pkl')

# DB CONFIG (Update 'root' and '' with your DB user/password if needed)
DB_CONNECTION_STR = 'mysql+mysqlconnector://root:@127.0.0.1/tradlanka_db'
db_engine = create_engine(DB_CONNECTION_STR)

# --- GLOBAL VARIABLES ---
model = None
feature_list = None
filenames = None
neighbors = None

# --- LOAD SYSTEM ---
def load_system():
    global model, feature_list, filenames, neighbors
    
    # 1. Load Visual Search Index
    if os.path.exists(INDEX_FILE):
        feature_list = pickle.load(open(INDEX_FILE, 'rb'))
        filenames = pickle.load(open(FILENAMES_FILE, 'rb'))
        
        base_model = MobileNetV2(weights='imagenet', include_top=False, input_shape=(IMG_WIDTH, IMG_HEIGHT, 3))
        base_model.trainable = False
        model = Sequential([base_model, GlobalMaxPooling2D()])

        neighbors = NearestNeighbors(n_neighbors=10, algorithm='brute', metric='euclidean')
        neighbors.fit(feature_list)
        print("[AI] Visual Search System Ready!")
    else:
        print("[AI] Warning: Index files not found. Run create_index.py first.")

load_system()

# --- INPUT MODELS ---
class RecommendRequest(BaseModel):
    filenames: list[str] # List of image paths the user viewed
    product_ids: list[int] # List of ID's (for Pandas logic)

# --- ENDPOINT 1: VISUAL SEARCH (Camera) ---
@app.post("/search")
async def search(image: UploadFile = File(...)):
    try:
        # Read Image
        contents = await image.read()
        img = image.load_img(io.BytesIO(contents), target_size=(IMG_WIDTH, IMG_HEIGHT))
        img_array = image.img_to_array(img)
        expanded_img_array = np.expand_dims(img_array, axis=0)
        preprocessed_img = preprocess_input(expanded_img_array)
        
        # Get Features
        query_features = model.predict(preprocessed_img, verbose=0).flatten()
        query_features = query_features / np.linalg.norm(query_features)
        
        # Find Matches
        distances, indices = neighbors.kneighbors([query_features])
        
        results = []
        for i in range(len(indices[0])):
            idx = indices[0][i]
            dist = distances[0][i]
            similarity = max(0, 1 - (dist / 1.4))
            results.append({"filename": filenames[idx], "similarity": float(similarity)})
            
        return results
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

# --- ENDPOINT 2: HYBRID RECOMMENDATIONS (Home Page) ---
@app.post("/recommend")
async def recommend(data: RecommendRequest):
    print(f"\n[REQ] Processing recommendation for: {data.product_ids}")
    
    recommendations = []
    
    # STRATEGY A: Try Pandas (User Behavior) FIRST
    try:
        # 1. Fetch History Data
        query = "SELECT user_id, product_id, count(*) as view_count FROM product_views GROUP BY user_id, product_id"
        df = pd.read_sql(query, db_engine)
        
        if len(df) > 5: # Only run if we have enough data
            user_item_matrix = df.pivot_table(index='user_id', columns='product_id', values='view_count').fillna(0)
            item_similarity = cosine_similarity(user_item_matrix.T)
            sim_df = pd.DataFrame(item_similarity, index=user_item_matrix.columns, columns=user_item_matrix.columns)
            
            # Find similar items to the last viewed product
            last_id = data.product_ids[0] if data.product_ids else None
            
            if last_id and last_id in sim_df.index:
                similar_products = sim_df[last_id].sort_values(ascending=False).iloc[1:6]
                # Convert Product IDs to Filenames (We need a DB lookup here, simplified for now)
                # For this step, we will return IDs and let Laravel handle it, OR fallback to Visual if Pandas fails
                print(f"[PANDAS] Found behavioral matches: {similar_products.index.tolist()}")
                # Note: Returning IDs here would require Laravel to handle IDs. 
                # To keep it simple, if Pandas works, we usually return IDs. 
                # BUT, since your Frontend expects 'filenames', let's stick to Strategy B (Visual) for now 
                # unless you update Laravel to accept IDs.
    except Exception as e:
        print(f"[PANDAS] Skipped (Not enough data or error): {e}")

    # STRATEGY B: Visual AI (The Fallback)
    # If Pandas didn't find anything (or we stick to Visual for consistency), we use the visual style.
    if not recommendations and data.filenames:
        print("[VISUAL] Using Visual AI fallback...")
        target_indices = []
        for fname in data.filenames:
            try:
                if fname in filenames:
                    target_indices.append(filenames.index(fname))
            except: continue
            
        if target_indices:
            selected_features = [feature_list[i] for i in target_indices]
            user_vector = np.mean(selected_features, axis=0)
            user_vector = user_vector / np.linalg.norm(user_vector)
            
            distances, indices = neighbors.kneighbors([user_vector], n_neighbors=10)
            
            for i in range(len(indices[0])):
                idx = indices[0][i]
                if filenames[idx] not in data.filenames:
                    recommendations.append({"filename": filenames[idx]})
    
    return recommendations

if __name__ == '__main__':
    uvicorn.run(app, host='127.0.0.1', port=5000)