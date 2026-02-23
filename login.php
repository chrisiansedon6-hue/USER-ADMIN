<?php
require_once 'config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    if (!empty($email) && !empty($password)) {
        $stmt = $conn->prepare("SELECT id, name, email, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];

            if ($remember) {
                setcookie('user_email', $user['email'], time() + (86400 * 30), "/");
            }

            header('Location: index.php');
            exit;
        } else {
            $error = "Invalid email or password";
        }
    } else {
        $error = "Please fill in all fields";
    }
}

$prefillEmail = $_COOKIE['user_email'] ?? '';
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Login - Artisan Pastries</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{
font-family:'Poppins',sans-serif;
height:100vh;
display:flex;
background:linear-gradient(135deg,#e17055,#fdcb6e);
overflow:hidden;
}

/* LEFT SIDE SLIDER */
.left{
flex:1;
color:white;
display:flex;
flex-direction:column;
justify-content:center;
align-items:center;
padding:2rem;
position:relative;
overflow:hidden;
}

.slider-text{
font-size:2rem;
font-weight:bold;
text-align:center;
opacity:0;
position:absolute;
transition:0.6s ease;
}

.slider-text.active{
opacity:1;
transform:translateY(0);
}

/* RIGHT LOGIN */
.right{
flex:1;
display:flex;
justify-content:center;
align-items:center;
background:#fff;
}

.auth-card{
width:100%;
max-width:400px;
background:rgba(255,255,255,0.9);
padding:2.5rem;
border-radius:20px;
box-shadow:0 20px 40px rgba(0,0,0,0.15);
}

.auth-card h2{
text-align:center;
margin-bottom:1.5rem;
color:#e17055;
}

input{
width:100%;
padding:0.8rem;
margin-bottom:1rem;
border-radius:10px;
border:1px solid #ddd;
}

input:focus{
border-color:#e17055;
outline:none;
box-shadow:0 0 8px rgba(225,112,85,0.3);
}

button{
width:100%;
padding:0.9rem;
border:none;
background:#e17055;
color:white;
font-weight:bold;
border-radius:10px;
cursor:pointer;
transition:0.3s;
}

button:hover{
background:#d35400;
transform:translateY(-2px);
}

.form-options{
display:flex;
justify-content:space-between;
font-size:0.85rem;
margin-bottom:1rem;
}

.link{
color:#e17055;
cursor:pointer;
}

.alert-error{
color:red;
margin-bottom:1rem;
text-align:center;
}

/* ADMIN MODAL */
.modal{
display:none;
position:fixed;
top:0;
left:0;
width:100%;
height:100%;
background:rgba(0,0,0,0.5);
justify-content:center;
align-items:center;
}

.modal-content{
background:white;
padding:2rem;
border-radius:15px;
width:350px;
animation:fadeIn 0.3s ease;
}

@keyframes fadeIn{
from{opacity:0;transform:scale(0.9);}
to{opacity:1;transform:scale(1);}
}

.close{
text-align:right;
cursor:pointer;
font-weight:bold;
color:red;
}
</style>
</head>
<body>
   <a href="index.php" class="back-home">
    <i class="fas fa-arrow-left"></i>
</a>

<!-- LEFT SLIDER -->
<div class="left">
<div class="slider-text active">Freshly Baked Every Day 🍞</div>
<div class="slider-text">Handcrafted Since 1985 🎂</div>
<div class="slider-text">Sweet Moments Start Here 🍰</div>
</div>

<!-- RIGHT LOGIN -->
<div class="right">
<div class="auth-card">
<h2>User Login</h2>

<?php if(isset($error)): ?>
<div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<form method="POST">
<input type="email" name="email" placeholder="Email" required value="<?php echo htmlspecialchars($prefillEmail); ?>">
<input type="password" name="password" placeholder="Password" required>

<div class="form-options">
<label><input type="checkbox" name="remember"> Remember</label>
<span class="link" onclick="openModal()">Admin Login</span>
</div>

<button type="submit" name="user_login">Sign In</button>
</form>

<p style="text-align:center;margin-top:1rem;">
No account? <a href="signup.php" class="link">Sign Up</a>
</p>

</div>
</div>

<!-- ADMIN MODAL -->
<div class="modal" id="adminModal">
<div class="modal-content">

<div class="close" onclick="closeModal()">✖</div>

<!-- LOGIN FORM -->
<div id="adminLoginForm">
<h3 style="text-align:center;margin-bottom:1rem;">Admin Login</h3>

<form method="POST" action="admin/admin_login.php">
<input type="email" name="email" placeholder="Admin Email" required>
<input type="password" name="password" placeholder="Admin Password" required>
<button type="submit">Login</button>
</form>

<p style="text-align:center;margin-top:1rem;">
No admin account? 
<span class="link" onclick="showSignup()">Sign Up</span>
</p>
</div>


<!-- SIGNUP FORM -->
<div id="adminSignupForm" style="display:none;">
<h3 style="text-align:center;margin-bottom:1rem;">Admin Signup</h3>

<form method="POST" action="admin/admin_signup.php">
<input type="text" name="name" placeholder="Admin Name" required>
<input type="email" name="email" placeholder="Admin Email" required>
<input type="password" name="password" placeholder="Password" required>
<input type="password" name="confirm_password" placeholder="Confirm Password" required>
<button type="submit">Create Account</button>
</form>

<p style="text-align:center;margin-top:1rem;">
Already have account? 
<span class="link" onclick="showLogin()">Login</span>
</p>
</div>

</div>
</div>

<script>
/* SLIDER LOOP */
const texts = document.querySelectorAll(".slider-text");
let index = 0;

setInterval(()=>{
texts[index].classList.remove("active");
index = (index + 1) % texts.length;
texts[index].classList.add("active");
},3000);

/* MODAL */
function openModal(){
document.getElementById("adminModal").style.display="flex";
}

function closeModal(){
document.getElementById("adminModal").style.display="none";
}

window.onclick=function(e){
if(e.target.id==="adminModal"){
closeModal();
}

}

function showSignup(){
document.getElementById("adminLoginForm").style.display = "none";
document.getElementById("adminSignupForm").style.display = "block";
}

function showLogin(){
document.getElementById("adminSignupForm").style.display = "none";
document.getElementById("adminLoginForm").style.display = "block";
}
</script>

</body>
</html>