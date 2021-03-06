<?php
// $conn = mysqli_connect("127.0.0.1","admin","password","products");
// if(!$conn){
//     die("<script>alert('SConnection Failed.')</script>");
// }
?>
<?php



class Products
{
    private $db;


    function __construct()
    {

        session_start();


        // $this->connect() = new mysqli("127.0.0.1","admin","password","products");


        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            if (isset($_POST["login"])) {
                $this->Login();
            }
            if (isset($_POST["register"])) {
                $this->Register();
            }
            if (isset($_POST["addproduct"])) {
                $this->Addproduct();
            }
            if (isset($_POST["product_id_for_rating"])) {
                $this->Rating();
            }
            if (isset($_POST["delete"])) {
                $this->Delete();
            }
            if (isset($_POST["update"])) {
                $this->Update();
            }
        }
    }

    public function connect()
    {
        $cleardb_url = parse_url(getenv("CLEARDB_DATABASE_URL"));
        $cleardb_server = $cleardb_url["host"];
        $cleardb_username = $cleardb_url["user"];
        $cleardb_password = $cleardb_url["pass"];
        $cleardb_db = substr($cleardb_url["path"], 1);
        $active_group = 'default';
        $query_builder = TRUE;

        $this->db = mysqli_connect($cleardb_server, $cleardb_username, $cleardb_password, $cleardb_db);
//   
        // $this->db = new mysqli("127.0.0.1","admin","password","products");

        return $this->db;
    }

    public function Login()
    {
        $email = $_POST['email'];
        $password = $_POST['password'];
        $errors = [];

        if (empty($email)) {
            $errors['emailerror'] = 'email field is required';
        } else {
            $user = $this->connect()->query("select * from users where email = '$email' ")->fetch_assoc();


            if ($user === NULL) {
                $errors['emailerror'] = "don't registering";
            } else if ($user['password'] !== $password) {
                $errors['emailerror'] = "wrong password";
            }
        }
        if (count($errors)) {
            $_SESSION['logerrors'] = $errors;
            $_SESSION['data'] = $_POST;
            header("Location:index.php");
        } else {
            $_SESSION['user'] = $user;
            header("Location:profile.php");
        }
    }

    public function Register()
    {
        $name = $_POST['name'];
        $surname = $_POST['surname'];
        $age = $_POST['age'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $cpassword = $_POST['cpassword'];
        $errors = [];

        if (empty($name)) {
            $errors['nameerror'] = 'Name field is required';
        }
        if (empty($surname)) {
            $errors['surnameerror'] = 'Surname field is required';
        }
        if (empty($age)) {
            $errors['ageerror'] = 'age field is required';
        } else if (!is_numeric($age)) {
            $errors['ageerror'] = 'age field must be number';
        }

        if (empty($email)) {
            $errors['emailerror'] = 'email field is required';
        } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['emailerror'] = 'email address must be valid';
        } else {
            $user = $this->connect()->query("select * from users where email = '$email' ");
            if ($user->fetch_assoc() !== NULL) {
                $errors['emailerror'] = 'email address already exists';
            }
        }

        if (empty($password)) {
            $errors['passworderror'] = 'password field is required';
        } else if (strlen($password) < 8) {
            $errors['passworderror'] = "password's must be equal or more than 8";
        }
        if ($password != $cpassword) {
            $errors['passworderror'] = 'password and confirm password must be equal';
        }

        if (count($errors)) {
            $_SESSION['regerrors'] = $errors;
            $_SESSION['data'] = $_POST;
            header("Location:register.php");
        } else {
            // print_r( $email );

            if ($email !== 'admin@gmail.com') {
                $this->connect()->query("INSERT INTO users (name,surname,age,email, password,type) VALUES ('$name', '$surname','$age','$email','$password',0)");
                header("Location:index.php");
            } else {
                $this->connect()->query("INSERT INTO users (name,surname,age,email, password,type) VALUES ('$name', '$surname','$age','$email','$password',1)");
                header("Location:index.php");
            }
            // $d = $this->connect()->query

            // print $d + 5;
            // print_r( mysqli_error($d) );
        }
    }
    public function Addproduct()
    {
        $errors = [];
        $name = $_POST['name'];
        $description  = $_POST['description'];
        $image  = $_POST['image'];
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
        $temp = explode(".", $_FILES["fileToUpload"]["name"]);
        $newfilename = round(microtime(true)) . '.' . end($temp);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        // $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
        // if ($check !== false) {
        //     // echo "File is an image - " . $check["mime"] . ".";
        //     $uploadOk = 1;
        // } else {
        //     $errors['imageerror'] = "File is not an image.";
        //     $uploadOk = 0;
        // }

        // if (file_exists($target_file)) {
        //         $errors['imageerror'] = "Sorry, file already exists.";
        //     $uploadOk = 0;
        // }



        if (
            $imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
            && $imageFileType != "gif"
        ) {
            $errors['imageerror'] = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            $uploadOk = 0;
        }

        // if ($uploadOk == 0) {
        //     // echo "Sorry, your file was not uploaded.";
        //     // if everything is ok, try to upload file
        // } else {
        //     if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
        //         // echo "The file ". htmlspecialchars( basename( $_FILES["fileToUpload"]["name"])). " has been uploaded.";
        //     }
        // }
        if (empty($name)) {
            $errors['nameerror'] = 'Name field is required';
        }else {
            $product = $this->connect()->query("select * from products where name = '$name' ")->fetch_assoc();


            if ($product != NULL) {
                $errors['nameerror'] = "Choose another name";
            } 
        }
        if (empty($description)) {
            $errors['descriptionerror'] = 'Description field is required';
        }

        if (count($errors)) {
            $_SESSION['adderrors'] = $errors;
            $_SESSION['data'] = $_POST;
            header("Location:addproduct.php");
        } else {
            $imagename = $_FILES["fileToUpload"]["name"];
            $this->connect()->query("INSERT INTO products (name,description,image) VALUES ('$name', '$description','$imagename')");

            $products = [];
            $query = "SELECT * FROM `products`";

            if ($sql = $this->connect()->query($query)) {
                while ($row = mysqli_fetch_assoc($sql)) {
                    //  array_push($products,$row );
                    $products[] = $row;
                }
            }

            $_SESSION['products'] = $products;

            header("Location:profile.php");
        }

        // print "<pre>";
        // print_r($errors);
    }

    public function Rating()
    {
        $comment = $_POST['comment'];
        $rating  = $_POST['rating'];
        $user_id = $_SESSION['user']['id'];
        $product_id = $_POST['product_id_for_rating'];
        if ($user_id) {
            if ($rating ) {
                $this->connect()->query("INSERT INTO ratings (user_id,product_id,rating,comment) VALUES ('$user_id', '$product_id','$rating','$comment')");
                header("Location:rating.php?id=$product_id");
            }
            // else if($comment){
            //     $this->connect()->query("INSERT INTO ratings (user_id,product_id,rating,comment) VALUES ('$user_id', '$product_id','$rating','$comment')");
            //     header("Location:rating.php?id=$product_id");
            // }
            else {
                header("Location:rating.php?id=$product_id");
            }
        } else {
            header("Location:index.php");
        }
        // print "<pre>";
        // print_r($comment);
    }

    public function Delete()
    {
        $product_id = $_POST['delete'];
        $this->connect()->query("DELETE FROM products WHERE id = $product_id");
        header("Location:profile.php");

        // print "<pre>";
        // print_r($product_id);
    }

    public function Update()
    {
        $product_id = $_POST['update'];
        $name = $_POST['name'];
        $description = $_POST['description'];
        $errors = [];   
        if (empty($name)) {
            $errors['nameerror'] = 'name field is required';
        } else if (empty($description)) {
            $errors['descriptionerror'] = 'description field is required';
        } else {
            $product = $this->connect()->query("select * from products where name = '$name' ")->fetch_assoc();


            if ($product != NULL) {
                $errors['nameerror'] = "Choose another name";
            }else{ 
                $this->connect()->query("UPDATE products SET name='$name', description='$description'  WHERE id=$product_id");
            }
        }
        if (count($errors)) {
            $_SESSION['updateerrors'] = $errors;
            $_SESSION['data'] = $_POST;
            header("Location:update.php?id=$product_id");
        } else {
            header("Location:profile.php");
        }
        
        // header("Location:profile.php");
        
        // print "<pre>";
        // print_r($errors);
    }
}
new Products();
?>