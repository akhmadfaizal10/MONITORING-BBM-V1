import pandas as pd
from flask import Flask, request, jsonify
from sklearn.model_selection import train_test_split
from sklearn.ensemble import RandomForestClassifier
import joblib
import os

app = Flask(__name__)

MODEL_PATH = "model.pkl"

def train_model():
    dataset_path = os.path.join(
        "C:/Users/mapple/Desktop/app/monitoring/public/data-training", 
        "dataset.xlsx"
    )
    df = pd.read_excel(dataset_path)

    # Pastikan kolom sesuai
    features = ["fuel_level", "fuel_in", "fuel_out"]
    target = "status"

    X = df[features]
    y = df[target]

    X_train, X_test, y_train, y_test = train_test_split(
        X, y, test_size=0.2, random_state=42
    )

    model = RandomForestClassifier(n_estimators=100, random_state=42)
    model.fit(X_train, y_train)

    acc = model.score(X_test, y_test)
    print(f"✅ Model trained with accuracy: {acc:.2f}")

    joblib.dump(model, MODEL_PATH)
    return model

# Load model kalau sudah ada, kalau belum latih baru
if os.path.exists(MODEL_PATH):
    model = joblib.load(MODEL_PATH)
    print("✅ Model loaded from file")
else:
    model = train_model()

@app.route("/predict", methods=["POST"])
def predict():
    data = request.json
    try:
        features = [[
            float(data["fuel_level"]),
            float(data["fuel_in"]),
            float(data["fuel_out"]),
        ]]
        prediction = model.predict(features)[0]
        return jsonify({"prediction": prediction})
    except Exception as e:
        return jsonify({"error": str(e)}), 400

if __name__ == "__main__":
    app.run(host="0.0.0.0", port=5001, debug=True)
