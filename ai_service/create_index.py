import os
import numpy as np
import pickle
from tensorflow.keras.applications.mobilenet_v2 import MobileNetV2, preprocess_input
from tensorflow.keras.preprocessing import image
from tensorflow.keras.layers import GlobalMaxPooling2D
from tensorflow.keras.models import Sequential
from tqdm import tqdm

# --- CONFIGURATION ---
img_width, img_height = 224, 224
storage_root = '../public/storage' 
target_folder = 'products' 

index_file = 'features.pkl'
filenames_file = 'filenames.pkl'

# --- MODEL SETUP ---
base_model = MobileNetV2(weights='imagenet', include_top=False, input_shape=(img_width, img_height, 3))
base_model.trainable = False
model = Sequential([base_model, GlobalMaxPooling2D()])
print("Model loaded successfully.")

# --- HELPER FUNCTION ---
def extract_features(img_path, model):
    try:
        img = image.load_img(img_path, target_size=(img_width, img_height))
        img_array = image.img_to_array(img)
        expanded_img_array = np.expand_dims(img_array, axis=0)
        preprocessed_img = preprocess_input(expanded_img_array)
        features = model.predict(preprocessed_img, verbose=0)
        flattened_features = features.flatten()
        normalized_features = flattened_features / np.linalg.norm(flattened_features)
        return normalized_features
    except Exception as e:
        return None

# --- MAIN LOOP ---
feature_list = []
product_files = [] 

full_target_path = os.path.join(storage_root, target_folder)
print(f"Scanning images in {full_target_path}...")

for root, dirs, files in os.walk(full_target_path):
    for filename in tqdm(files):
        if filename.lower().endswith(('.png', '.jpg', '.jpeg', '.webp')):
            full_path = os.path.join(root, filename)
            # Save relative path (e.g., products/gallery/img.jpg)
            relative_path = os.path.relpath(full_path, storage_root).replace("\\", "/")
            
            features = extract_features(full_path, model)
            if features is not None:
                feature_list.append(features)
                product_files.append(relative_path)

pickle.dump(feature_list, open(index_file, 'wb'))
pickle.dump(product_files, open(filenames_file, 'wb'))
print(f"Saved to {index_file} and {filenames_file}")