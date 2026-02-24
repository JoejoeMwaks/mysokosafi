<?php
// Entry point and basic router
session_start();

require_once __DIR__ . '/config/db.php'; // $pdo may be null if not configured
require_once __DIR__ . '/includes/db_functions.php';
require_once __DIR__ . '/includes/mpesa.php';

// Ensure CSRF token exists (lazy init in generate_csrf_token but checking here is fine too)
if (!isset($_SESSION['csrf_token'])) {
    generate_csrf_token();
}

// Basic router
$page = $_GET['page'] ?? 'home';
$allowed = ['home','product','products','cart','checkout','login','register','logout','mpesa_pay','mpesa_callback','cart_add','contact','faq','shipping','returns','featured','new_arrivals'];
if (!in_array($page, $allowed, true)) {
    $page = 'home';
}

// Global CSRF verification for POST requests (after $page is set)
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $exempt_pages = ['mpesa_callback']; // External webhooks
    if (!in_array($page, $exempt_pages, true)) {
        $token = $_POST['csrf_token'] ?? '';
        if (!verify_csrf_token($token)) {
            // For AJAX requests like cart_add, return JSON error
            $is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
            
            if ($page === 'cart_add') {
                header('Content-Type: application/json');
                echo json_encode(['ok' => false, 'error' => 'Invalid security token. Please refresh the page.']);
                exit;
            }
            
            // Generic failure
            $_SESSION['flash'] = 'Security check failed. Please try again.';
            header('Location: index.php?page=login');
            exit;
        }
    }
}

// Handle logout before any output
if ($page === 'logout') {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
    header('Location: index.php?page=home');
    exit;
}

// Handle auth POST (login/register) before output for proper redirects
$auth_error = null;
if ($page === 'login' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    // Simple session-based rate limiting: max 5 attempts in 15 minutes
    $now = time();
    $window = 15 * 60; // 15 minutes
    $max_attempts = 5;
    if (!isset($_SESSION['login_attempts'])) { $_SESSION['login_attempts'] = []; }
    // Keep only attempts within window
    $_SESSION['login_attempts'] = array_values(array_filter($_SESSION['login_attempts'], function($t) use ($now, $window) { return ($now - $t) < $window; }));
    if (count($_SESSION['login_attempts']) >= $max_attempts) {
        $remaining = $window - ($now - $_SESSION['login_attempts'][0]);
        $mins = max(1, (int)floor($remaining / 60));
        $auth_error = 'Too many login attempts. Please try again in about ' . $mins . ' minute(s).';
    }
    if ($email === '' || $password === '') {
        $auth_error = 'Please enter your email and password.';
    } else {
        if ($auth_error === null) { // only proceed if not locked out
            $user = get_user_by_email($email);
        } else {
            $user = null;
        }
        if ($user && isset($user['password']) && password_verify($password, $user['password'])) {
            $display_name = trim((($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')));
            if ($display_name === '') {
                $display_name = $user['email'] ?? 'Account';
            }
            $_SESSION['user'] = [
                'id' => (int)$user['id'],
                'name' => $display_name,
                'email' => $user['email'] ?? '',
                'roles' => isset($user['roles']) && $user['roles'] !== null ? explode(', ', $user['roles']) : []
            ];
            // Reset attempts on success
            $_SESSION['login_attempts'] = [];
            $next = $_POST['next'] ?? ($_GET['next'] ?? null);
            if ($next && strpos($next, '://') === false) {
                header('Location: ' . $next);
            } else {
                header('Location: index.php?page=home');
            }
            exit;
        } else {
            $auth_error = 'Invalid email or password.';
            // Record failed attempt
            $_SESSION['login_attempts'][] = $now;
        }
    }
}

if ($page === 'register' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($name === '' || $email === '' || $password === '') {
        $auth_error = 'Name, email, and password are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $auth_error = 'Please enter a valid email address.';
    } else {
        $existing = get_user_by_email($email);
        if ($existing) {
            $auth_error = 'An account with this email already exists.';
        } else {
            $user_id = create_user($name, $email, $password);
            if ($user_id) {
                $user = get_user_by_email($email);
                $_SESSION['user'] = [
                    'id' => (int)$user_id,
                    'name' => $name,
                    'email' => $email,
                    'roles' => isset($user['roles']) && $user['roles'] !== null ? explode(', ', $user['roles']) : ['customer']
                ];
                header('Location: index.php?page=home');
                exit;
            } else {
                $auth_error = 'Failed to create account. Please try again.';
            }
        }
    }
}

// Handle add to cart from product page
$cart_error = null;
// JSON API for add-to-cart without redirect
if ($page === 'cart_add' && (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') && isset($_POST['add_to_cart'])) {
    header('Content-Type: application/json');
    if (!isset($_SESSION['user']['id'])) { echo json_encode(['ok' => false, 'redirect' => 'index.php?page=login']); exit; }
    $product_id = (int)($_POST['product_id'] ?? 0);
    $quantity = max(1, (int)($_POST['quantity'] ?? 1));
    if ($product_id <= 0) {
        echo json_encode(['ok' => false, 'error' => 'Invalid product selection.']);
        exit;
    }
    if (add_item_to_cart_unified($product_id, $quantity)) {
        // Fetch cart count for update
        $user_id = isset($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : null;
        $cart = $user_id ? get_user_cart($user_id) : get_session_cart();
        $count = 0;
        if ($cart && !empty($cart['items'])) {
            foreach ($cart['items'] as $it) { $count += (int)$it['quantity']; }
        }
        echo json_encode(['ok' => true, 'cart_count' => $count]);
        exit;
    } else {
        echo json_encode(['ok' => false, 'error' => 'Unable to add item to cart.']);
        exit;
    }
}

// Support add-to-cart from any page with the expected POST fields (non-AJAX)
if ((($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') && isset($_POST['add_to_cart']) && $page !== 'cart_add') {
    if (!isset($_SESSION['user']['id'])) { $_SESSION['flash'] = 'Please login to add items to your cart.'; header('Location: index.php?page=login'); exit; }
    $product_id = (int)($_POST['product_id'] ?? 0);
    $quantity = max(1, (int)($_POST['quantity'] ?? 1));
    if ($product_id <= 0) {
        $cart_error = 'Invalid product selection.';
    } else {
    if (add_item_to_cart_unified($product_id, $quantity)) {
        header('Location: index.php?page=cart');
        exit;
    } else {
        $cart_error = 'Unable to add item to cart. Please try again.';
    }
    }
}

// Handle cart actions (remove item / update quantity)
if ($page === 'cart' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $action = $_POST['action'] ?? '';
    $item_id = (int)($_POST['item_id'] ?? 0);
    $quantity = max(1, (int)($_POST['quantity'] ?? 1));
    if (isset($_SESSION['user'])) {
        $user_id = (int)$_SESSION['user']['id'];
        if ($action === 'remove_item') {
            if ($item_id > 0 && remove_cart_item($user_id, $item_id)) {
                header('Location: index.php?page=cart');
                exit;
            } else {
                $cart_error = 'Unable to remove item. Please try again.';
            }
        } elseif ($action === 'update_quantity') {
            if ($item_id > 0 && update_cart_item_quantity($user_id, $item_id, $quantity)) {
                header('Location: index.php?page=cart');
                exit;
            } else {
                $cart_error = 'Unable to update quantity. Please try again.';
            }
        }
    } else {
        // Guest cart actions
        if ($action === 'remove_item') {
            if ($item_id > 0 && remove_cart_item_guest($item_id)) {
                header('Location: index.php?page=cart');
                exit;
            } else {
                $cart_error = 'Unable to remove item. Please try again.';
            }
        } elseif ($action === 'update_quantity') {
            if ($item_id > 0 && update_cart_item_quantity_guest($item_id, $quantity)) {
                header('Location: index.php?page=cart');
                exit;
            } else {
                $cart_error = 'Unable to update quantity. Please try again.';
            }
        }
    }
}

// Handle checkout order placement BEFORE rendering to avoid header issues
if ($page === 'checkout' && (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') && isset($_POST['place_order'])) {
    $checkout_error = null;
    $user = $_SESSION['user'] ?? null;
    if (!$user) {
        $checkout_error = 'Please login to proceed to checkout.';
    } else {
        $user_id = (int)$user['id'];
        $cart = get_user_cart($user_id);
        if (!$cart || empty($cart['items'])) {
            $checkout_error = 'Your cart is empty.';
        } else {
            // Payment method validation
            $payment_method = strtolower(trim($_POST['payment_method'] ?? 'mpesa'));
            $allowed_methods = ['mpesa','paypal','card','bank'];
            if (!in_array($payment_method, $allowed_methods, true)) {
                $payment_method = 'mpesa';
            }

            // Address selection/creation
            $selected_address_id = (int)($_POST['address_id'] ?? 0);
            if ($selected_address_id <= 0) {
                $new_label = trim($_POST['label'] ?? 'shipping');
                $new_line1 = trim($_POST['line1'] ?? '');
                $new_line2 = trim($_POST['line2'] ?? '');
                $new_city = trim($_POST['city'] ?? '');
                $new_state = trim($_POST['state'] ?? '');
                $new_postal = trim($_POST['postal_code'] ?? '');
                $new_country = trim($_POST['country'] ?? '');
                $make_default = isset($_POST['is_default']) ? 1 : 0;
                if ($new_line1 === '' || $new_city === '' || $new_state === '' || $new_postal === '' || $new_country === '') {
                    $checkout_error = 'Please provide a valid address or select an existing one.';
                } else {
                    $addr_id = create_address($user_id, $new_label, $new_line1, $new_line2, $new_city, $new_state, $new_postal, $new_country, $make_default);
                    if ($addr_id) {
                        $selected_address_id = (int)$addr_id;
                    } else {
                        $checkout_error = 'Failed to save address. Please try again.';
                    }
                }
            }

            if (!$checkout_error && $selected_address_id > 0) {
                $delivery_distance_km = isset($_POST['delivery_distance_km']) && $_POST['delivery_distance_km'] !== ''
                    ? (float)$_POST['delivery_distance_km']
                    : null;
                $totals = calculate_cart_totals($cart, $delivery_distance_km);
                $order_id = create_order_from_cart($user_id, $selected_address_id, $delivery_distance_km);
                if ($order_id) {
                    // Create payment row
                    $currency = ($payment_method === 'mpesa') ? 'KES' : 'USD';
                    $payment_id = create_payment($order_id, $payment_method, (float)$totals['total'], $currency, 'pending');
                    if ($payment_id) {
                        // If MPESA and phone provided, attempt STK push immediately
                        if ($payment_method === 'mpesa') {
                            $phone = trim($_POST['mpesa_phone'] ?? '');
                            if ($phone !== '') {
                                $res = mpesa_stk_push($order_id, (float)$totals['total'], $phone, $MPESA_ACCOUNT_REF, $MPESA_TXN_DESC);
                                if ($res['ok']) {
                                    // Store CheckoutRequestID as transaction_id for matching later
                                    $checkout_req_id = $res['data']['CheckoutRequestID'] ?? null;
                                    if ($checkout_req_id) {
                                        update_payment_status($payment_id, 'pending', $checkout_req_id);
                                    }
                                    $_SESSION['flash'] = 'Order placed! STK push sent to your phone.';
                                } else {
                                    $_SESSION['flash'] = 'Order placed! Pending payment via M-Pesa. ' . ($res['error'] ?? '');
                                }
                            } else {
                                $_SESSION['flash'] = 'Order placed! Pending payment via M-Pesa. Enter phone to initiate STK push.';
                            }
                        } else {
                            $_SESSION['flash'] = 'Order placed! Payment pending via ' . strtoupper($payment_method) . '.';
                        }
                    } else {
                        $_SESSION['flash'] = 'Order placed! Failed to initialize payment record.';
                    }
                    header('Location: index.php?page=home');
                    exit;
                } else {
                    $checkout_error = 'Failed to place order. Please try again.';
                }
            }
        }
    }
    // Bubble error to checkout page if something failed
    if ($checkout_error) {
        $_SESSION['checkout_error'] = $checkout_error;
        header('Location: index.php?page=checkout');
        exit;
    }
}

// Initiate M-Pesa STK Push for an existing order (manual trigger)
if ($page === 'mpesa_pay' && (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST')) {
    $user = $_SESSION['user'] ?? null;
    if (!$user) {
        $_SESSION['flash'] = 'Please login to pay for an order.';
        header('Location: index.php?page=login');
        exit;
    }
    $order_id = (int)($_POST['order_id'] ?? 0);
    $phone = trim($_POST['mpesa_phone'] ?? '');
    if ($order_id <= 0 || $phone === '') {
        $_SESSION['flash'] = 'Provide order and phone to start payment.';
        header('Location: index.php?page=home');
        exit;
    }
    // Fetch payment for this order
    $stmt = $pdo->prepare('SELECT id, amount, currency, status FROM payments WHERE order_id = ? ORDER BY id DESC LIMIT 1');
    $stmt->execute([$order_id]);
    $pay = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$pay) {
        $_SESSION['flash'] = 'No payment found for this order.';
        header('Location: index.php?page=home');
        exit;
    }
    if ($pay['status'] === 'completed') {
        $_SESSION['flash'] = 'Payment already completed.';
        header('Location: index.php?page=home');
        exit;
    }
    // Initiate STK push
    $amount = (float)$pay['amount'];
    $res = mpesa_stk_push($order_id, $amount, $phone, $MPESA_ACCOUNT_REF, $MPESA_TXN_DESC);
    if ($res['ok']) {
        // Store CheckoutRequestID
        $checkout_req_id = $res['data']['CheckoutRequestID'] ?? null;
        if ($checkout_req_id) {
            update_payment_status($pay['id'], 'pending', $checkout_req_id);
        }
        $_SESSION['flash'] = 'STK push sent. Check your phone.';
    } else {
        $_SESSION['flash'] = 'Failed to initiate M-Pesa: ' . ($res['error'] ?? '');
    }
    header('Location: index.php?page=home');
    exit;
}

// Receive M-Pesa STK callback (Daraja posts JSON)
if ($page === 'mpesa_callback' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $raw = file_get_contents('php://input');
    $json = json_decode($raw, true);
    if (!$json) {
        http_response_code(400);
        echo json_encode(['error' => 'invalid json']);
        exit;
    }
    $parsed = mpesa_extract_callback($json);
    $receipt = $parsed['receipt'] ?? null;
    $resultCode = $parsed['result_code'] ?? null;
    $checkoutId = $parsed['checkout_id'] ?? null;

    if ($checkoutId) {
        // Match payment by CheckoutRequestID (stored in transaction_id)
        $stmt = $pdo->prepare("SELECT id, order_id FROM payments WHERE transaction_id = ? AND status = 'pending' LIMIT 1");
        $stmt->execute([$checkoutId]);
        $payment = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($payment) {
            $new_status = ((int)$resultCode === 0) ? 'completed' : 'failed';
            // Update status. If receipt is null (failure), keep/use CheckoutRequestID as trace.
            $final_tx = $receipt ? $receipt : $checkoutId;
            update_payment_status((int)$payment['id'], $new_status, $final_tx);
        }
    }
    // Respond per Daraja expectation
    header('Content-Type: application/json');
    echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    exit;
}

// Render layout
include __DIR__ . '/includes/header.php';

echo "<main class=\"container\">";
// Prevent rendering non-page endpoints
if ($page === 'mpesa_pay' || $page === 'mpesa_callback') {
    $page = 'home';
}
include __DIR__ . "/pages/{$page}.php";
echo "</main>";

include __DIR__ . '/includes/footer.php';