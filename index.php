<?php require_once('header.php'); ?>
<?php include_once('error.php'); ?>
<?php
if(isset($_POST['submit'])){
    try {
        $food = htmlspecialchars($_POST['food']);
        $food = trim($_POST['food']);
        if (preg_match('/\s/', $food)) {
            throw new Exception("Food input must be only one word.");
        }
        $amount = htmlspecialchars($_POST['amount']);        
        
        $api_url = 'https://api.nal.usda.gov/fdc/v1/foods/search';
        $api_key = apikey();
    
        $data = [
            'query' => $food,
            'pageSize' => 1,
            'api_key' => $api_key
        ];
    
        // Initialize cURL and execute the API request
        $ch = curl_init($api_url . '?' . http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        if ($response === false) {
            throw new Exception('cURL error: ' . curl_error($ch));
        }
        curl_close($ch);
    
        $result = json_decode($response, true);
    
        if (!isset($result['foods'][0]['foodNutrients'])) {
            throw new Exception("No nutrient data found for {$food}.");
        }

        $measure = number_format($result['foods'][0]['servingSize'], 2);
        $servingSizeUnit = $result['foods'][0]['servingSizeUnit'];
        echo "The serving size for $food is $measure $servingSizeUnit.<br>";

        $total = $measure * $amount;
    
        // Initialize nutrient variables
        $calories = $protein = $fat = $carbohydrates = $fiber = $sugars = null;
        $cholesterol = $sodium = $vitamin_c = $calcium = $iron = null;
        $vitamin_b12 = $potassium = $magnesium = $nitrogen = $alcohol = null;
    
        foreach ($result['foods'][0]['foodNutrients'] as $nutrient) {
            $value = $nutrient['value'];
            switch ($nutrient['nutrientName']) {
                case 'Potassium, K':
                    if ($nutrient['unitName'] === 'MG') {
                        $potassium = $value;
                        echo "The Potassium content for $food is $value milligrams.<br>";
                    }
                    break;
                case 'Magnesium':
                    if ($nutrient['unitName'] === 'MG') {
                        $magnesium = $value;
                        echo "The Magnesium content for $food is $value milligrams.<br>";
                    }
                    break;
                case 'Water':
                    if ($nutrient['unitName'] === 'G') {
                        $water = $value;
                        echo "The water content for $food is $value grams.<br>";
                    }
                    break;
                case 'Energy':
                    if ($nutrient['unitName'] === 'KCAL') {
                        $calories = $value;
                        echo "The calorie count for $food is $value calories.<br>";
                    }
                    break;
                case 'Protein':
                    if ($nutrient['unitName'] === 'G') {
                        $protein = $value;
                        echo "The protein content for $food is $value grams.<br>";
                    }
                    break;
                case 'Total lipid (fat)':
                    if ($nutrient['unitName'] === 'G') {
                        $fat = $value;
                        echo "The fat content for $food is $value grams.<br>";
                    }
                    break;
                case 'Carbohydrate, by difference':
                    if ($nutrient['unitName'] === 'G') {
                        $carbohydrates = $value;
                        echo "The carbohydrate content for $food is $value grams.<br>";
                    }
                    break;
                case 'Fiber, total dietary':
                    if ($nutrient['unitName'] === 'G') {
                        $fiber = $value;
                        echo "The fiber content for $food is $value grams.<br>";
                    }
                    break;
                case 'Sugars, total including NLEA':
                    if ($nutrient['unitName'] === 'G') {
                        $sugars = $value;
                        echo "The sugar content for $food is $value grams.<br>";
                    }
                    break;
                case 'Cholesterol':
                    if ($nutrient['unitName'] === 'MG') {
                        $cholesterol = $value;
                        echo "The cholesterol content for $food is $value milligrams.<br>";
                    }
                    break;
                case 'Sodium, Na':
                    if ($nutrient['unitName'] === 'MG') {
                        $sodium = $value;
                        echo "The sodium content for $food is $value milligrams.<br>";
                    }
                    break;
                case 'Vitamin C, total ascorbic acid':
                    if ($nutrient['unitName'] === 'MG') {
                        $vitamin_c = $value;
                        echo "The vitamin C content for $food is $value milligrams.<br>";
                    }
                    break;
                case 'Calcium, Ca':
                    if ($nutrient['unitName'] === 'MG') {
                        $calcium = $value;
                        echo "The calcium content for $food is $value milligrams.<br>";
                    }
                    break;
                case 'Iron, Fe':
                    if ($nutrient['unitName'] === 'MG') {
                        $iron = $value;
                        echo "The iron content for $food is $value milligrams.<br>";
                    }
                    break;
            }
        }
    
        // Prepare and bind the database statement
        $stmt = $con->prepare("INSERT INTO food_plan (name, amount, measurement, total, calories, protein, fat, carbohydrates, fiber, sugars, cholesterol, sodium, vitamin_c, calcium, iron, potassium, magnesium, nitrogen) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssidssssssssssssss", $food, $measure, $amount, $total, $calories, $protein, $fat, $carbohydrates, $fiber, $sugars, $cholesterol, $sodium, $vitamin_c, $calcium, $iron, $potassium, $magnesium, $nitrogen);
    
        if (!$stmt->execute()) {
            throw new Exception("Database error: " . $stmt->error);
        }
    
        echo "<div class='alert alert-success'>Record inserted successfully</div>";
        $stmt->close();
    } catch (Exception $e) {
        // Log the error and display a user-friendly message
        error_log($e->getMessage());
        echo "<div class='alert alert-danger'>An error occurred: " . $e->getMessage() . "</div>";
    }
}
?>




<h1>Enter food to calculate nutrition</h1>

<form action="" method="post">
   <div class="form-group form-group-lg">
     <label for="food">Enter food item (only 1 word):</label>
     <input type="text" id="food" name="food" class="form-control"  placeholder="apples / eggs" required pattern="^\S+$" required>
   </div>
   <div class="form-group form-group-lg">
     <label for="amount">Enter how many:</label>
     <input type="number" id="amount" name="amount" class="form-control"  required>
   </div>
   <div class="form-group form-group-lg">
   <label for="measure">Choose Measurement Scale:</label>
     <select id="measure" name="measure" class="form-control" required>
       <option value="">Choose</option>
       <option value="240">Cup(s) 240 G</option>
       <option value="120">1/2 Cup(s) 120 G</option>
       <option value="3899">Gallon(s) 3899 G</option>
       <option value="1000">Liter(s) 1000 G</option>
       <option value="38">Slice(s) 38 G</option>
       <option value="50">Piece(s) 50 G</option>
       <option value="5.69">Teaspoon(s) 5.69 G</option>
       <option value="14.175">Tablespoon(s) 14.175 G</option>
       <option value="28.35">Ounce(s) 28.35 G</option>
       <option value="113">Stick(s) 113 G</option>
       <option value="800">Loaf(s) 800 G</option>
       <option value="106">Can(s) 3.75oz 106 G</option>
       <option value="425.24">Can(s) 15oz 425.24 G</option>
       <option value="340.19">Can(s) 12oz 340.19 G</option>
     </select>
   </div>
       <button type="submit" name="submit" class="btn btn-success">Add Food</button>
   </form>
<?php require_once('footer.php'); ?>
