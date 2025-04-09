<?php
session_start();

// Initialize response
$response = ['success' => false, 'message' => ''];

// Check if cart exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
    $response['message'] = 'Cart is empty';
    echo json_encode($response);
    exit;
}

// Handle remove item
if (isset($_POST['remove']) && isset($_POST['index'])) {
    $index = $_POST['index'];
    
    if (isset($_SESSION['cart'][$index])) {
        array_splice($_SESSION['cart'], $index, 1);
        $response['success'] = true;
        $response['message'] = 'Item removed from cart';
    } else {
        $response['message'] = 'Item not found in cart';
    }
}
// Handle quantity update
else if (isset($_POST['index']) && isset($_POST['change'])) {
    $index = $_POST['index'];
    $change = intval($_POST['change']);
    
    if (isset($_SESSION['cart'][$index])) {
        $newQuantity = $_SESSION['cart'][$index]['quantity'] + $change;
        
        if ($newQuantity <= 0) {
            // Remove item if quantity becomes 0 or negative
            array_splice($_SESSION['cart'], $index, 1);
            $response['success'] = true;
            $response['message'] = 'Item removed from cart';
        } else {
            // Update quantity
            $_SESSION['cart'][$index]['quantity'] = $newQuantity;
            $response['success'] = true;
            $response['message'] = 'Cart updated';
        }
    } else {
        $response['message'] = 'Item not found in cart';
    }
} else {
    $response['message'] = 'Invalid request';
}

echo json_encode($response);
?>