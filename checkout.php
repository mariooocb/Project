<?php

include 'components/connect.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
   header('location:user_login.php');
};

if(isset($_POST['order'])){

   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $number = $_POST['number'];
   $number = filter_var($number, FILTER_SANITIZE_STRING);
   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);
   $method = $_POST['method'];
   $method = filter_var($method, FILTER_SANITIZE_STRING);
   $address = 'flat no. '. $_POST['flat'] .', '. $_POST['street'] .', '. $_POST['city'] .', '. $_POST['state'] .', '. $_POST['country'] .' - '. $_POST['pin_code'];
   $address = filter_var($address, FILTER_SANITIZE_STRING);
   $total_products = $_POST['total_products'];
   $total_price = $_POST['total_price'];

   $check_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
   $check_cart->execute([$user_id]);

   if($check_cart->rowCount() > 0){

      $insert_order = $conn->prepare("INSERT INTO `orders`(user_id, name, number, email, method, address, total_products, total_price) VALUES(?,?,?,?,?,?,?,?)");
      $insert_order->execute([$user_id, $name, $number, $email, $method, $address, $total_products, $total_price]);

      $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
      $delete_cart->execute([$user_id]);

      $message[] = 'Pedido realizado con éxito!';
   }else{
      $message[] = 'Su Carrito esta Vacio!';
   }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Website Design FlashPollos</title>
   
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<section class="checkout-orders">

   <form action="" method="POST">

   <h3>Tus Ordenes</h3>

      <div class="display-orders">
      <?php
         $grand_total = 0;
         $cart_items[] = '';
         $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
         $select_cart->execute([$user_id]);
         if($select_cart->rowCount() > 0){
            while($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)){
               $cart_items[] = $fetch_cart['name'].' ('.$fetch_cart['price'].' x '. $fetch_cart['quantity'].') - ';
               $total_products = implode($cart_items);
               $grand_total += ($fetch_cart['price'] * $fetch_cart['quantity']);
      ?>
         <p> <?= $fetch_cart['name']; ?> <span>(<?= '$'.$fetch_cart['price'].'/- x '. $fetch_cart['quantity']; ?>)</span> </p>
      <?php
            }
         }else{
            echo '<p class="empty">Su Carrito esta Vacio!</p>';
         }
      ?>
         <input type="hidden" name="total_products" value="<?= $total_products; ?>">
         <input type="hidden" name="total_price" value="<?= $grand_total; ?>" value="">
         <div class="grand-total">Total : <span>$<?= $grand_total; ?>/-</span></div>
      </div>

      <h3>realizar pedidos</h3>

      <div class="flex">
         <div class="inputBox">
            <span>Tu Nombre :</span>
            <input type="text" name="name" placeholder="Nombre" class="box" maxlength="20" required>
         </div>
         <div class="inputBox">
            <span>Tu Numero :</span>
            <input type="number" name="number" placeholder="Numero" class="box" min="0" max="9999999999" onkeypress="if(this.value.length == 10) return false;" required>
         </div>
         <div class="inputBox">
            <span>Tu Email :</span>
            <input type="email" name="email" placeholder="Email" class="box" maxlength="50" required>
         </div>
         <div class="inputBox">
            <span>Metodo de Pago :</span>
            <select name="method" class="box" required>
               <option value="cash on delivery">Pago Contra Entrega</option>
               <option value="credit card">Tarjeta de Credito</option>
               <option value="paytm">Paypal</option>
               <option value="paypal">Otros...</option>
            </select>
         </div>
         <div class="inputBox">
            <span>Dirección 01 :</span>
            <input type="text" name="flat" placeholder="MzE. Casa#1, Arkala, Ibagué" class="box" maxlength="50" required>
         </div>
         <div class="inputBox">
            <span>Dirección line 02 :</span>
            <input type="text" name="street" placeholder="MzE. Casa#1, Arkala, Ibagué" class="box" maxlength="50" required>
         </div>
         <div class="inputBox">
            <span>Ciudad :</span>
            <input type="text" name="city" placeholder="Ibagué" class="box" maxlength="50" required>
         </div>
         <div class="inputBox">
            <span>Departamento :</span>
            <input type="text" name="state" placeholder="Tolima" class="box" maxlength="50" required>
         </div>
         <div class="inputBox">
            <span>Pais :</span>
            <input type="text" name="country" placeholder="Colombia" class="box" maxlength="50" required>
         </div>
         <div class="inputBox">
            <span>Codigo Postal :</span>
            <input type="number" min="0" name="pin_code" placeholder="123456" min="0" max="999999" onkeypress="if(this.value.length == 6) return false;" class="box" required>
         </div>
      </div>

      <input type="submit" name="order" class="btn <?= ($grand_total > 1)?'':'disabled'; ?>" value="Comprar">

   </form>

</section>

<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>