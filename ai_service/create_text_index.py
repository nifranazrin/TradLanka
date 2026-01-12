import json
import pickle
from sklearn.feature_extraction.text import TfidfVectorizer

# 1. Load Data
try:
    with open('products.json', 'r') as f:
        products = json.load(f)
except FileNotFoundError:
    print("Error: products.json missing. Run /export-products in Laravel.")
    exit()

print(f"Training on {len(products)} products...")

# 2. Prepare Text
product_ids = [p['id'] for p in products]
corpus = [p['text'] for p in products] 

# --- NEW: Define Custom Stop Words ---
# These are words the AI should IGNORE because they appear in almost every product.
my_stop_words = [
    'english', 
    'ceylon', 'sri', 'lanka', 'tradlanka', 
    'benefits', 'origin', 'material', 'size', 'color', 'colour',
    'hand', 'made', 'handmade', 'product', 'item', 'natural', 'pure', 'premium', 'quality',
    'antioxidants', 'antioxidant', 'properties', 'flavor', 'flavour', 'aroma', 'aromatic',
    'traditional', 'supports', 'healthy', 'rich', 'essential', 'known', 'used',
    'green' 
]

# 3. Create Vectors (TF-IDF)
# We pass our custom list to stop_words
vectorizer = TfidfVectorizer(stop_words=my_stop_words, max_features=5000)
tfidf_matrix = vectorizer.fit_transform(corpus)

# 4. Save Index
pickle.dump(tfidf_matrix, open('text_features.pkl', 'wb'))
pickle.dump(product_ids, open('product_ids.pkl', 'wb'))

print("Success! Index created with Custom Stop Words.")