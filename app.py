from flask import Flask, render_template, request, redirect, url_for

app = Flask(__name__)

# Datos de ejemplo para "Platos Nutricionales"
dishes = [
    {"id": 1, "name": "Ensalada de Quinoa", "image": "https://via.placeholder.com/150?text=Plato+1"},
    {"id": 2, "name": "Pasta Integral", "image": "https://via.placeholder.com/150?text=Plato+2"},
    {"id": 3, "name": "Sopa de Vegetales", "image": "https://via.placeholder.com/150?text=Plato+3"},
]

# Estadísticas de uso para la página de inicio (simuladas)
usage_stats = {"total_users": 150, "dishes_checked": 75}

@app.route("/")
def homepage():
    return render_template("homepage.html", stats=usage_stats)

@app.route("/login", methods=["GET", "POST"])
def login():
    if request.method == "POST":
        username = request.form.get("username")
        password = request.form.get("password")
        # Aquí se podría llamar a una función PHP para validar el usuario.
        # Se asume autenticación correcta y se redirige a la página principal.
        return redirect(url_for("homepage"))
    return render_template("login.html")

@app.route("/ideal_weight", methods=["GET", "POST"])
def ideal_weight():
    result = None
    if request.method == "POST":
        height = float(request.form.get("height", 0))
        age = float(request.form.get("age", 0))
        # Fórmula de ejemplo para calcular el peso ideal
        result = (height - 100 + age / 10) * 0.9
    return render_template("ideal_weight.html", result=result)

@app.route("/search", methods=["GET", "POST"])
def search():
    if request.method == "POST":
        food = request.form.get("food")
        amount = request.form.get("amount")
        # Se redirige a la ruta que muestra el informe nutricional.
        return redirect(url_for("report", food=food, amount=amount))
    return render_template("search.html")

@app.route("/report")
def report():
    food = request.args.get("food", "Desconocido")
    amount = request.args.get("amount", "0")
    # Se simula un informe nutricional. En un caso real, se invocarían funciones PHP.
    try:
        amt = float(amount)
    except ValueError:
        amt = 1
    nutritional_report = {
        "calories": 100 * amt,
        "proteins": 5 * amt,
        "fats": 3 * amt,
        "carbohydrates": 15 * amt,
    }
    return render_template("report.html", food=food, amount=amount, report=nutritional_report)

@app.route("/platos")
def platos():
    return render_template("platos.html", dishes=dishes)

@app.route("/plato/<int:dish_id>")
def plato_detail(dish_id):
    dish = next((d for d in dishes if d["id"] == dish_id), None)
    if not dish:
        return "Plato no encontrado", 404
    # Datos simulados para los ingredientes y la información nutricional del plato.
    ingredients = ["Ingrediente A", "Ingrediente B", "Ingrediente C"]
    nutritional_info = {"calories": 300, "proteins": 10, "fats": 5, "carbohydrates": 40}
    return render_template("plato_detail.html", dish=dish, ingredients=ingredients, nutritional=nutritional_info)

if __name__ == "__main__":
    app.run(debug=True)
