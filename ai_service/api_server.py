from flask import Flask, request, jsonify
import numpy as np
import pickle
import os
import io
from tensorflow.keras.applications.mobilenet_v2 import MobileNetV2, preprocess_input
from tensorflow.keras.preprocessing import image
from tensorflow.keras.layers import GlobalMaxPooling2D
from tensorflow.keras.models import Sequential
from sklearn.neighbors import NearestNeighbors

app = Flask(__name__)

IMG_WIDTH, IMG_HEIGHT = 224, 224
BASE_PATH = os.path.dirname(os.path.abspath(__file__))
INDEX_FILE = os.path.join(BASE_PATH, 'features.pkl')
FILENAMES_FILE = os.path.join(BASE_PATH, 'filenames.pkl')

model = None
feature_list = None
filenames = None
neighbors = None

def load_system():
    global model, feature_list, filenames, neighbors
    if not os.path.exists(INDEX_FILE):
        print("Index file not found. Run create_index.py first.")
        return

    feature_list = pickle.load(open(INDEX_FILE, 'rb'))
    filenames = pickle.load(open(FILENAMES_FILE, 'rb'))
    
    base_model = MobileNetV2(weights='imagenet', include_top=False, input_shape=(IMG_WIDTH, IMG_HEIGHT, 3))
    base_model.trainable = False
    model = Sequential([base_model, GlobalMaxPooling2D()])

    neighbors = NearestNeighbors(n_neighbors=8, algorithm='brute', metric='euclidean')
    neighbors.fit(feature_list)
    print("AI System Ready!")

load_system()

@app.route('/search', methods=['POST'])
def search():
    if 'image' not in request.files:
        return jsonify({"error": "No image"}), 400
    try:
        file = request.files['image']
        img_bytes = io.BytesIO(file.read())
        img = image.load_img(img_bytes, target_size=(IMG_WIDTH, IMG_HEIGHT))
        img_array = image.img_to_array(img)
        expanded_img_array = np.expand_dims(img_array, axis=0)
        preprocessed_img = preprocess_input(expanded_img_array)
        
        query_features = model.predict(preprocessed_img, verbose=0).flatten()
        query_features = query_features / np.linalg.norm(query_features)
        
        distances, indices = neighbors.kneighbors([query_features])
        
        results = []
        for i in range(len(indices[0])):
            idx = indices[0][i]
            dist = distances[0][i]
            similarity = max(0, 1 - (dist / 1.4))
            results.append({"filename": filenames[idx], "similarity": float(similarity)})
            
        return jsonify(results)
    except Exception as e:
        return jsonify({"error": str(e)}), 500

if __name__ == '__main__':
    app.run(host='127.0.0.1', port=5000)