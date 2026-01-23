import json
import pickle
import os
from sklearn.feature_extraction.text import TfidfVectorizer

# Define paths relative to this script
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
JSON_PATH = os.path.join(BASE_DIR, 'products.json')
TEXT_IDX_PATH = os.path.join(BASE_DIR, 'text_features.pkl')
IDS_PATH = os.path.join(BASE_DIR, 'product_ids.pkl')

def generate_text_index():
    print(" [Text AI] Starting training...")
    
    # 1. Load Data
    if not os.path.exists(JSON_PATH):
        print(f"Error: {JSON_PATH} missing.")
        return False

    with open(JSON_PATH, 'r') as f:
        products = json.load(f)

    print(f" [Text AI] Training on {len(products)} products...")

    # 2. Prepare Text
    product_ids = [p['id'] for p in products]
    corpus = [p['text'] for p in products] 

    # 3. Stop Words
    my_stop_words = [
        'english', 'ceylon', 'sri', 'lanka', 'tradlanka', 
        'benefits', 'origin', 'material', 'size', 'color', 'colour',
        'hand', 'made', 'handmade', 'product', 'item', 'natural', 'pure', 
        'premium', 'quality', 'antioxidants', 'antioxidant', 'properties', 
        'flavor', 'flavour', 'aroma', 'aromatic', 'traditional', 'supports', 
        'healthy', 'rich', 'essential', 'known', 'used', 'green' 
    ]

    # 4. Create Vectors
    vectorizer = TfidfVectorizer(stop_words=my_stop_words, max_features=5000)
    tfidf_matrix = vectorizer.fit_transform(corpus)

    # 5. Save Index
    pickle.dump(tfidf_matrix, open(TEXT_IDX_PATH, 'wb'))
    pickle.dump(product_ids, open(IDS_PATH, 'wb'))

    print(" [Text AI] Success! Index created.")
    return True

if __name__ == "__main__":
    generate_text_index()