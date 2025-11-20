# ✅ FULL CODE OF app.py ✅
from flask import Flask, request, jsonify, render_template_string
import mysql.connector
import bcrypt
import jwt
import datetime
from flask_cors import CORS, cross_origin
import torch
import torch.nn as nn
import numpy as np
import pandas as pd
import random
import traceback


app = Flask(__name__)
app.config["SECRET_KEY"] = "93d5efa03f2babbfafda3660a3f265751961cbd9094a1b8d22ee0c65fc5a2d88"
CORS(app, supports_credentials=True, resources={r"/*": {"origins": "http://localhost"}})

bgl_mean = 129.20
bgl_std = 41.64
bmi_mean = 24.77
bmi_std = 4.52

file_path = "exercise_data.csv"
ep = pd.read_csv(file_path)  

file_path = "diabetes_diet_dataset.csv"
dp = pd.read_csv(file_path)  


class HybridModel(nn.Module):
    def __init__(self, input_size=3, hidden_size=128):
        super(HybridModel, self).__init__()
        
        self.fm_linear = nn.Linear(input_size, 2)
        
        self.lstm = nn.LSTM(input_size, hidden_size, batch_first=True, num_layers=2, dropout=0.2)
        self.fc = nn.Linear(hidden_size, 2)
        self.dropout = nn.Dropout(0.2)
        
        self.layer_norm = nn.LayerNorm(hidden_size)

        self.fc.bias.data.fill_(0.12)

    def forward(self, x):
        batch_size = x.shape[0]
        
        fm_output = self.fm_linear(x[:, -1, :])
        
        lstm_out, _ = self.lstm(x)
        lstm_output = self.fc(self.dropout(self.layer_norm(lstm_out[:, -1, :])))
        
        output = fm_output + lstm_output
        
        output = output + 0.15
        
        return output


# Instantiate the model with correct hidden_size
model = HybridModel(input_size=3, hidden_size=128)
model.load_state_dict(torch.load('hybrid_model.pth'))
model.eval()


input_size = 3  # For example, 3 features: Age, BGL, BMI
hidden_size = 128  # used during training




# Database connection
try:
    db = mysql.connector.connect(
        host="localhost", user="root", password="", database="DiabetesManagementDB"
    )
    cursor = db.cursor(dictionary=True)
    print("✅ Database connected successfully!")
except mysql.connector.Error as err:
    print(f"❌ Database connection failed: {err}")


def verify_token(token):
    try:
        return jwt.decode(token, app.config["SECRET_KEY"], algorithms=["HS256"])
    except jwt.ExpiredSignatureError:
        return {"error": "Token expired"}
    except jwt.InvalidTokenError:
        return {"error": "Invalid token"}


@app.route("/")
def home():
    return render_template_string("""
        <html> 
        <head><title>Diabetes Management</title></head> 
        <style> 
            
        </style>                          
        <body> 
            <iframe src="http://localhost/DRS/template/PHP/index.php" width="100%" height="1000px" style="border:none; "></iframe> 
        </body> 
        </html>
    """)


@app.route("/verify_token", methods=["POST"])
def verify_token_endpoint():
    data = request.json
    token = data.get("token")
    if not token:
        return jsonify({"error": "Token missing"}), 400

    user_data = verify_token(token)
    if "error" in user_data:
        return jsonify({"error": user_data["error"]}), 401

    return jsonify({"message": "Token is valid", "user_data": user_data}), 200


@app.route('/register', methods=['POST'])
def register_patient():
    try:
        data = request.json
        username = data.get('username')
        email = data.get('email')
        password = data.get('password')
        age = data.get('age')
        gender = data.get('gender')

        if not username or not email or not password or not age or not gender:
            return jsonify({"error": "Missing required fields"}), 400

        cursor.execute("SELECT email FROM patients WHERE email = %s", (email,))
        if cursor.fetchone():
            return jsonify({"error": "Email already registered"}), 409

        hashed_password = bcrypt.hashpw(password.encode('utf-8'), bcrypt.gensalt()).decode('utf-8')

        cursor.execute(
            "INSERT INTO patients (username, email, password, age, gender, created_at) VALUES (%s, %s, %s, %s, %s, NOW())",
            (username, email, hashed_password, age, gender)
        )
        db.commit()

        return jsonify({"success": True, "message": "Patient registered successfully!"}), 201

    except mysql.connector.Error as err:
        db.rollback()
        return jsonify({"error": f"Database error: {err}"}), 500


@app.route("/login", methods=["POST"])
def login():
    data = request.json
    email = data.get("email")
    password = data.get("password")

    if not email or not password:
        return jsonify({"error": "Missing email or password"}), 400

    try:
        cursor.execute("SELECT * FROM patients WHERE email = %s", (email,))
        patient = cursor.fetchone()
        if patient and bcrypt.checkpw(password.encode(), patient["password"].encode()):
            token = jwt.encode(
                {"user_id": patient["patient_id"], "user_type": "patient", "exp": datetime.datetime.utcnow() + datetime.timedelta(hours=1)},
                app.config["SECRET_KEY"], algorithm="HS256"
            )
            return jsonify({"message": "Login successful!", "token": token, "redirect": "/patient_dashboard"}), 200

        cursor.execute("SELECT * FROM admins WHERE email = %s", (email,))
        admin = cursor.fetchone()
        if admin and password == admin["password"]:
            token = jwt.encode(
                {"user_id": admin["admin_id"], "user_type": "admin", "exp": datetime.datetime.utcnow() + datetime.timedelta(hours=1)},
                app.config["SECRET_KEY"], algorithm="HS256"
            )
            return jsonify({"message": "Login successful!", "token": token, "redirect": "/admin_dashboard"}), 200

        return jsonify({"error": "Invalid email or password"}), 401

    except mysql.connector.Error as err:
        return jsonify({"error": str(err)}), 500


@app.route("/admin_dashboard")
def admin_dashboard():
    token = request.args.get("token")
    if not token:
        return jsonify({"error": "Missing token"}), 401

    user_data = verify_token(token)
    if "error" in user_data or user_data.get("user_type") != "admin":
        return jsonify({"error": "Unauthorized"}), 403

    username = user_data.get("username", "Admin")

    html = f"""
    <html>
    <head><title>Admin Dashboard</title></head>
    <body>
        <iframe src="http://localhost/DRS/template/PHP/admin_dashboard.php?token={token}&username={username}" width="100%" height="1000px" style="border:none; overflow: hidden;"></iframe>
    </body>
    </html>
    """
    return html


def assign_level_based_on_bgl(predicted_bgl):
    if predicted_bgl < 104:
        return "Level 0"
    elif 104 <= predicted_bgl < 126:
        return "Level 1"
    elif 126 <= predicted_bgl < 180:
        return "Level 2"
    elif 180 <= predicted_bgl < 240:
        return "Level 3"
    return "🚨 Immediate Medical Attention Required!"

def categorize_bmi(predicted_bmi):
    if predicted_bmi < 18.5:
        return "Easy"
    elif predicted_bmi < 30:
        return "Intermediate"
    elif predicted_bmi < 45:
        return "Difficult"
    return "Obese"

def get_diet_plan(diabetes_level, bmi_category):
    """Fetch a diet plan based on diabetes level and BMI category."""
    diabetes_level = diabetes_level.strip().title()
    bmi_category = bmi_category.strip().capitalize()

    print(f"🔍 Fetching diet plan for Diabetes Level: {diabetes_level}, BMI Category: {bmi_category}")
    
    # ✅ Debug: Print unique values
    print("🔍 Unique Diabetes Levels in dataset:", dp["Level"].unique())
    print("🔍 Unique BMI Categories in dataset:", dp["Meal Type"].unique())

    # ✅ Ensure correct filtering by normalizing case and stripping spaces
    filtered_dp = dp[
        (dp["Level"].str.strip().str.title() == diabetes_level) & 
        (dp["Meal Type"].str.strip().str.capitalize() == bmi_category)
    ]

    if filtered_dp.empty:
        print("❌ No matching diet plan found in dataset.")
        return "No diet plan available.", "No diet plan available.", "No diet plan available."

    print(f"✅ Found {len(filtered_dp)} matching diet records.")

    # ✅ Extract values safely
    breakfast = filtered_dp.iloc[0]["Breakfast"] if "Breakfast" in filtered_dp.columns else "No data"
    lunch = filtered_dp.iloc[0]["Lunch"] if "Lunch" in filtered_dp.columns else "No data"
    dinner = filtered_dp.iloc[0]["Dinner"] if "Dinner" in filtered_dp.columns else "No data"

    return breakfast, lunch, dinner





def get_exercise_plan(diabetes_level, bmi_category):
    """Fetch an exercise plan based on diabetes level and BMI category."""
    diabetes_level = diabetes_level.strip().lower().replace(" ", "_")
    bmi_category = bmi_category.strip().lower().replace(" ", "_")

    print(f"🔍 Fetching exercise plan for Diabetes Level: {diabetes_level}, BMI Category: {bmi_category}")

    # ✅ Debugging: Print dataset values
    print("🔍 Unique Diabetes Levels in dataset:", ep["Level"].unique())
    print("🔍 Unique BMI Categories in dataset:", ep["Category"].unique())

    # ✅ Normalize case before filtering
    ep["Level"] = ep["Level"].str.strip().str.lower().str.replace(" ", "_")
    ep["Category"] = ep["Category"].str.strip().str.lower().str.replace(" ", "_")

    filtered_ep = ep[
        (ep["Level"] == diabetes_level) & 
        (ep["Category"] == bmi_category)
    ]

    if filtered_ep.empty:
        print("❌ No matching exercise plan found in dataset.")
        return {"Set 1": "No exercise available", "Set 2": "No exercise available", "Set 3": "No exercise available"}

    print(f"✅ Found {len(filtered_ep)} matching exercise plans.")

    # ✅ Ensure we get random exercises for each set
    exercise_plan = {}
    for set_number in ["Set 1", "Set 2", "Set 3"]:
        set_exercises = filtered_ep[filtered_ep["Set"] == set_number]["Exercise"].tolist()
        exercise_plan[set_number] = random.choice(set_exercises) if set_exercises else "No exercise available"

    return exercise_plan  # Returns a dictionary with three sets





def diabetes_advice(diabetes_level):
    advice = {
        "Level 0 (Non-Diabetic)": "Maintain a healthy lifestyle and regular checkups.",
        "Level 1 (Pre-Diabetes)": "Increase physical activity and avoid processed sugars.",
        "Level 2 (Normal Diabetes)": "Monitor diet closely and maintain an active lifestyle.",
        "Level 3 (High Diabetes)": "Consult a doctor. Follow a strict low-carb diet.",
        "🚨 Immediate Medical Attention Required!": "Seek emergency medical care immediately."
    }
    return advice.get(diabetes_level, "Consult a doctor for a medical plan.")




# Define the patient_dashboard route
@app.route('/patient_dashboard', methods=['GET', 'POST'])
def patient_dashboard():
    if request.method == 'GET':
        token = request.args.get('token')
        raw_result_str = request.args.get('raw_result', '')
        html = f"""
        <html>
        <head><title>Patient Dashboard</title></head>
        <body>
            <iframe src="http://localhost/DRS/template/PHP/patient_dashboard.php?token={token}&raw_result={raw_result_str}" width="100%" height="1000px" style="border:none; overflow: hidden; "></iframe>
        </body>
        </html>
        """
        return html

    elif request.method == 'POST':
        data = request.get_json()
        reports = data.get('data')
        patient_id = data.get('patient_id')

        if not reports or len(reports) != 7:
            return jsonify({"error": "Invalid or missing reports data"}), 400

        try:
            # Extract age
            age = reports[0]

            # ✅ Normalize BGL and BMI before passing to model
            reshaped_reports = np.array([
                [age, (reports[1] - bgl_mean) / bgl_std, (reports[2] - bmi_mean) / bmi_std],
                [age, (reports[3] - bgl_mean) / bgl_std, (reports[4] - bmi_mean) / bmi_std],
                [age, (reports[5] - bgl_mean) / bgl_std, (reports[6] - bmi_mean) / bmi_std]
            ], dtype=np.float32).reshape(1, 3, 3)

            tensor_input = torch.tensor(reshaped_reports)

            # ✅ Model Prediction
            with torch.no_grad():
                output = model(tensor_input).numpy().flatten()

            # ✅ Denormalize predictions
            predicted_bgl = (output[0] * bgl_std) + bgl_mean
            predicted_bmi = (output[1] * bmi_std) + bmi_mean

            # Get Recommendations
            diabetes_level = assign_level_based_on_bgl(predicted_bgl)

            # Categorize BMI
            bmi_category = categorize_bmi(predicted_bmi)

            # Fetch diet plan (split into breakfast, lunch, dinner)
            breakfast, lunch, dinner = get_diet_plan(diabetes_level, bmi_category)

            # Fetch exercise plan
            exercise_plan = get_exercise_plan(diabetes_level, bmi_category)

            # Get specific diabetes advice
            diabetes_specific_advice = diabetes_advice(diabetes_level)

            # Prepare the response data
            result = {
                "predicted_bgl": predicted_bgl,
                "predicted_bmi": predicted_bmi,
                "diabetes_level": diabetes_level,
                "bmi_category": bmi_category,
                "diet_plan": {
                    "breakfast": breakfast,
                    "lunch": lunch,
                    "dinner": dinner
                },
                "exercise_plan": exercise_plan,
                "diabetes_specific_advice": diabetes_specific_advice
            }

            return jsonify(result)

        except Exception as e:
            print("❌ Error in /patient_dashboard:", traceback.format_exc())  # Print full traceback
            return jsonify({"error": str(e)}), 500



@app.before_request
def handle_preflight():
    if request.method == 'OPTIONS':
        response = jsonify({'status': 'ok'})
        response.headers['Access-Control-Allow-Origin'] = 'http://localhost'
        response.headers['Access-Control-Allow-Methods'] = 'GET, POST, OPTIONS'
        response.headers['Access-Control-Allow-Headers'] = 'Content-Type, Authorization'
        return response




if __name__ == "__main__":
    app.run(debug=True)
