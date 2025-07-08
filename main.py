import tensorflow as tf
import numpy as np
import argparse
import os
import requests
from io import BytesIO
from PIL import Image
from tensorflow.keras.preprocessing.image import img_to_array
import matplotlib.pyplot as plt

# ✅ Class names from your dataset
class_names = [
    'bicycle', 'bolt', 'book', 'bus', 'car', 'cloud', 'fire', 'gem',
    'gift', 'helicopter', 'leaf', 'lightbulb', 'moon', 'motorcycle',
    'plane', 'rocket', 'ship', 'snowflake', 'star', 'sun',
    'train', 'tree', 'truck', 'umbrella', 'water'
]

img_size = (64, 64)
model_path = "icon_classifier_model.h5"

# ✅ Load the model
if not os.path.exists(model_path):
    print(f"❌ Model file not found: {model_path}")
    exit(1)

model = tf.keras.models.load_model(model_path)

# ✅ Argument parser
parser = argparse.ArgumentParser(description="Icon Classifier")
parser.add_argument("-i", "--image", help="Path to local image file")
parser.add_argument("-u", "--url", help="URL of image file")
args = parser.parse_args()

# ✅ Load image from local file
def load_local_image(path):
    if not os.path.exists(path):
        print(f"❌ File not found: {path}")
        exit(1)
    return Image.open(path).convert("RGB")

# ✅ Load image from URL
def load_url_image(url):
    try:
        response = requests.get(url)
        img = Image.open(BytesIO(response.content)).convert("RGB")
        return img
    except Exception as e:
        print(f"❌ Failed to download image: {e}")
        exit(1)

# ✅ Predict function
def predict_image(img):
    img_resized = img.resize(img_size)
    arr = img_to_array(img_resized) / 255.0
    arr = np.expand_dims(arr, axis=0)

    pred = model.predict(arr)
    class_id = np.argmax(pred)
    confidence = np.max(pred)

    print(f"🔍 Predicted Class: {class_names[class_id]}")
    print(f"📊 Confidence: {confidence:.2f}")

    plt.imshow(img)
    plt.title(f"{class_names[class_id]} ({confidence:.2f})")
    plt.axis('off')
    plt.show()

# ✅ Decision logic
if args.image:
    image = load_local_image(args.image)
    predict_image(image)
elif args.url:
    image = load_url_image(args.url)
    predict_image(image)
else:
    print("❗ Please provide either -i (local image) or -u (image URL)")
    parser.print_help()
