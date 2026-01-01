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

# 3. Create Vectors (TF-IDF)
vectorizer = TfidfVectorizer(stop_words='english', max_features=5000)
tfidf_matrix = vectorizer.fit_transform(corpus)

# 4. Save Index
pickle.dump(tfidf_matrix, open('text_features.pkl', 'wb'))
pickle.dump(product_ids, open('product_ids.pkl', 'wb'))

print("Success! Index created.")