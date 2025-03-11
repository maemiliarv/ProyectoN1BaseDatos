from flask import Flask, render_template, url_for

app = Flask(__name__, template_folder="templates", static_folder="static")

# Datos de ejemplo
recipes = [
    {"name": "Chopped Spring Ramen", "calories": 250, "ingredients": "Scallions & tomatoes", "image": "recipe.jpg"},
    {"name": "Chicken Tandoori", "calories": 450, "ingredients": "Chicken & Salad", "image": "tandoori.png"}
]

# Página principal
@app.route('/', endpoint='home')
def home():
    return render_template("home.html", recipes=recipes)

# Página de favoritos
@app.route('/favorites', endpoint='favorites')
def favorites():
    return render_template("favorites.html", recipes=recipes)

# Página de escaneo
@app.route('/scan')
def scan():
    return render_template("scan.html")

# Página de perfil
@app.route('/profile')
def profile():
    return render_template("profile.html")

# Layout base
@app.route('/layout')
def layout():
    return render_template("layout.html")

if __name__ == '__main__':
    app.run(debug=True)
