import os
import numpy as np
import pickle
from tensorflow.keras.applications.mobilenet_v2 import MobileNetV2, preprocess_input
from tensorflow.keras.preprocessing import image
from tensorflow.keras.layers import GlobalAveragePooling2D
from tensorflow.keras.models import Sequential
from tqdm import tqdm

# Config
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
STORAGE_ROOT = os.path.abspath(os.path.join(BASE_DIR, '../public/storage'))
TARGET_FOLDER = 'products'
INDEX_FILE = os.path.join(BASE_DIR, 'features.pkl')
FILENAMES_FILE = os.path.join(BASE_DIR, 'filenames.pkl')
IMG_WIDTH, IMG_HEIGHT = 224, 224

def extract_features(img_path, model):
    try:
        img = image.load_img(img_path, target_size=(IMG_WIDTH, IMG_HEIGHT))
        img_array = image.img_to_array(img)
        expanded_img_array = np.expand_dims(img_array, axis=0)
        preprocessed_img = preprocess_input(expanded_img_array)
        features = model.predict(preprocessed_img, verbose=0)
        flattened_features = features.flatten()
        return flattened_features / np.linalg.norm(flattened_features)
    except:
        return None

def generate_visual_index():
    print(" [Visual AI] Starting training...")
    
    # Load Model inside function to avoid memory hogging if not used
    base_model = MobileNetV2(weights='imagenet', include_top=False, input_shape=(IMG_WIDTH, IMG_HEIGHT, 3))
    base_model.trainable = False
    model = Sequential([base_model, GlobalAveragePooling2D()])
    
    feature_list = []
    product_files = [] 
    full_target_path = os.path.join(STORAGE_ROOT, TARGET_FOLDER)

    if not os.path.exists(full_target_path):
        print(f" [Visual AI] Error: Directory {full_target_path} not found.")
        return False

    print(f" [Visual AI] Scanning images in {full_target_path}...")

    # Walk through files
    for root, dirs, files in os.walk(full_target_path):
        for filename in tqdm(files):
            if filename.lower().endswith(('.png', '.jpg', '.jpeg', '.webp')):
                full_path = os.path.join(root, filename)
                relative_path = os.path.relpath(full_path, STORAGE_ROOT).replace("\\", "/")
                
                features = extract_features(full_path, model)
                if features is not None:
                    feature_list.append(features)
                    product_files.append(relative_path)

    pickle.dump(feature_list, open(INDEX_FILE, 'wb'))
    pickle.dump(product_files, open(FILENAMES_FILE, 'wb'))
    print(f" [Visual AI] Saved to {INDEX_FILE}")
    return True

if __name__ == "__main__":
    generate_visual_index()