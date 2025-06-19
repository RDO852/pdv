<?php
$senha = '123'; // Sua senha em texto puro
$hash = password_hash($senha, PASSWORD_BCRYPT);
echo "Senha: " . $senha . "<br>";
echo "Hash: " . $hash . "<br>";
?>