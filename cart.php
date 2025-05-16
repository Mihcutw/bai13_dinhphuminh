<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Kết nối cơ sở dữ liệu
$servername = "localhost";
$username = "root"; // Thay bằng username của bạn
$password = ""; // Thay bằng password của bạn
$dbname = "bigshop"; // Thay bằng tên cơ sở dữ liệu của bạn

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Xử lý xóa sản phẩm khỏi giỏ hàng
if (isset($_POST['remove_from_cart'])) {
    $product_id = $_POST['product_id'];
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
        header("Location: cart.php");
        exit();
    }
}

// Xử lý cập nhật số lượng
if (isset($_POST['update_quantity'])) {
    $product_id = $_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    if (isset($_SESSION['cart'][$product_id]) && $quantity > 0) {
        $_SESSION['cart'][$product_id]['quantity'] = $quantity;
        header("Location: cart.php");
        exit();
    }
}

// Lấy dữ liệu giỏ hàng từ session
$cart_items = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$total = 0;

include 'header.php'; // Giả định header.php đã có
?>

<style>
    .cart-container {
        width: 80%;
        margin: 0 auto;
        padding: 20px;
    }
    .cart-item {
        display: flex;
        align-items: center;
        border-bottom: 1px solid #ddd;
        padding: 10px 0;
        margin-bottom: 10px;
    }
    .cart-item img {
        width: 100px;
        height: auto;
        margin-right: 20px;
    }
    .cart-item .details {
        flex-grow: 1;
    }
    .cart-item .details p {
        margin: 5px 0;
    }
    .cart-item .price {
        color: #ff6200;
        font-weight: bold;
    }
    .cart-item .quantity-input {
        width: 60px;
        padding: 5px;
        margin-right: 10px;
    }
    .cart-item .update-btn, .cart-item .remove-btn {
        background-color: #ff6200;
        color: white;
        padding: 5px 10px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        margin-right: 10px;
    }
    .cart-item .remove-btn {
        background-color: #ff0000;
    }
    .cart-item .update-btn:hover, .cart-item .remove-btn:hover {
        opacity: 0.8;
    }
    .cart-total {
        font-size: 18px;
        font-weight: bold;
        text-align: right;
        margin-top: 20px;
    }
    .empty-cart {
        text-align: center;
        font-size: 18px;
        color: #666;
    }
</style>

<div class="cart-container">
    <h2>Giỏ Hàng</h2>
    <?php
    if (!empty($cart_items)) {
        foreach ($cart_items as $product_id => $item) {
            // Lấy thông tin sản phẩm từ cơ sở dữ liệu để đảm bảo dữ liệu chính xác
            $sql = "SELECT * FROM products WHERE id = $product_id";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                $product = $result->fetch_assoc();
                $subtotal = $product['price'] * $item['quantity'];
                $total += $subtotal;

                echo "<div class='cart-item'>";
                echo "<img src='{$product['image']}' alt='{$product['name']}'>";
                echo "<div class='details'>";
                echo "<p><strong>{$product['name']}</strong></p>";
                echo "<p class='price'>" . number_format($product['price'], 0, ',', '.') . " đ</p>";
                echo "</div>";
                echo "<form method='POST' action='' style='display: flex; align-items: center;'>";
                echo "<input type='number' name='quantity' class='quantity-input' value='{$item['quantity']}' min='1'>";
                echo "<input type='hidden' name='product_id' value='{$product_id}'>";
                echo "<button type='submit' name='update_quantity' class='update-btn'>Cập nhật</button>";
                echo "<button type='submit' name='remove_from_cart' class='remove-btn'>Xóa</button>";
                echo "</form>";
                echo "</div>";
            }
        }
        echo "<div class='cart-total'>Tổng cộng: " . number_format($total, 0, ',', '.') . " đ</div>";
    } else {
        echo "<p class='empty-cart'>Giỏ hàng của bạn đang trống.</p>";
    }
    ?>
</div>

<?php
$conn->close();
include 'footer.php'; // Giả định footer.php đã có
?>