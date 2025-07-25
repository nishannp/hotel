<?php 
// payments.php - The final "Cashier's Workstation" with itemized receipts and toast notifications
require_once 'includes/header.php'; 
$loggedInAdminId = $_SESSION['admin_id'] ?? 0;
echo "<script>document.querySelector('.content-header h1').textContent = 'Payment Workstation';</script>";
?>

<div class="payment-workstation-container">
    <!-- Left Panel: The Queue of Unpaid Orders -->
    <aside class="payment-queue-sidebar">
        <div class="payment-queue-header">
            Pending Payments Queue
        </div>
        <div class="payment-queue-body" id="paymentQueue"></div>
    </aside>

    <!-- Right Panel: The Payment Terminal -->
    <main class="payment-terminal-main">
        <div class="payment-terminal-header" id="terminalHeader">Select an Order</div>
        <div class="payment-terminal-body" id="terminalBody">
            <div id="welcomeView">
                <p style="text-align: center; color: #777; margin-top: 50px;"><i class="fas fa-arrow-left" style="margin-right: 10px;"></i>Please select an order from the queue.</p>
            </div>
            
            <form id="paymentForm" style="display: none;">
                <input type="hidden" id="orderIdInput" name="order_id">
                <input type="hidden" id="staffIdInput" name="staff_id" value="<?php echo $loggedInAdminId; ?>">

                <!-- NEW: Itemized Receipt Display -->
                <div class="payment-form-group">
                    <label>Itemized Receipt</label>
                    <div class="receipt-items-container" id="receiptItems"></div>
                </div>

                <div class="payment-form-group">
                    <label for="amountPaid">Amount to Pay</label>
                    <input type="number" id="amountPaid" name="amount_paid" step="0.01" required>
                </div>
                <div class="payment-form-group">
                    <label for="paymentMethod">Payment Method</label>
                    <select id="paymentMethod" name="payment_method" required>
                        <option value="Cash">Cash</option>
                        <option value="Credit Card">Credit Card</option>
                        <option value="Debit Card">Debit Card</option>
                        <option value="Online Payment">Online Payment</option>
                    </select>
                </div>
                <div class="payment-form-group">
                    <label for="transactionId">Transaction ID (Optional)</label>
                    <input type="text" id="transactionId" name="transaction_id">
                </div>
                <div style="margin-top: 20px;">
                    <button type="submit" id="confirmPaymentBtn" class="confirm-payment-btn">Confirm Payment</button>
                </div>
            </form>
        </div>
    </main>
</div>

<script>
$(document).ready(function() {
    const ajaxUrl = 'ajax/ajax_handler_payments.php';
    const queueContainer = $('#paymentQueue');
    const terminalHeader = $('#terminalHeader');
    const paymentForm = $('#paymentForm');
    const welcomeView = $('#welcomeView');
    const confirmBtn = $('#confirmPaymentBtn');
    let allOrdersData = []; // Cache the fetched data

    // --- NEW: Toast Notification Function ---
    function showToast(message, isError = false) {
        // Create toast element
        const toast = $('<div></div>')
            .addClass('toast-notification')
            .text(message);

        if (isError) {
            toast.addClass('error');
        }

        // Add to body and animate in
        $('body').append(toast);
        setTimeout(() => {
            toast.addClass('show');
        }, 100); // Small delay to allow CSS transition

        // Remove after 3 seconds
        setTimeout(() => {
            toast.removeClass('show');
            // Remove from DOM after transition ends
            toast.on('transitionend', () => toast.remove());
        }, 3000);
    }

    function loadUnpaidOrders() {
        queueContainer.html('<p style="text-align:center; padding: 20px;">Loading...</p>');
        welcomeView.show();
        paymentForm.hide();
        terminalHeader.text('Select an Order');
        
        $.getJSON(`${ajaxUrl}?action=getUnpaidOrders`, function(data) {
            queueContainer.empty();
            if (data.success && data.data.length > 0) {
                allOrdersData = data.data; // Cache the data
                allOrdersData.forEach(order => {
                    const orderCard = $(`
                        <div class="order-card" data-order-id="${order.OrderID}">
                            <div class="order-card-header">
                                <span>Order #${order.OrderID}</span>
                                <span style="color:var(--primary-color);">$${parseFloat(order.TotalAmount).toFixed(2)}</span>
                            </div>
                            <div class="order-card-body">
                                Table ${order.TableNumber} • ${new Date(order.OrderTime).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                            </div>
                        </div>
                    `);
                    queueContainer.append(orderCard);
                });
            } else {
                allOrdersData = [];
                queueContainer.html('<p style="text-align:center; padding: 20px;">The payment queue is empty. Great job!</p>');
            }
        });
    }

    queueContainer.on('click', '.order-card', function() {
        const card = $(this);
        const orderId = card.data('orderId');
        
        // Find the full order data from our cache
        const selectedOrder = allOrdersData.find(o => o.OrderID == orderId);
        if (!selectedOrder) return;

        $('.order-card').removeClass('selected');
        card.addClass('selected');

        welcomeView.hide();
        paymentForm.show();
        paymentForm[0].reset();

        terminalHeader.text(`Processing Payment for Order #${orderId}`);
        $('#orderIdInput').val(orderId);
        $('#amountPaid').val(parseFloat(selectedOrder.TotalAmount).toFixed(2));

        // --- NEW: Populate the itemized receipt ---
        const receiptContainer = $('#receiptItems').empty();
        if (selectedOrder.items && selectedOrder.items.length > 0) {
            selectedOrder.items.forEach(item => {
                receiptContainer.append(`
                    <div class="receipt-item">
                        <span class="item-name">${item.quantity}x ${item.name}</span>
                        <span class="item-subtotal">$${parseFloat(item.subtotal).toFixed(2)}</span>
                    </div>
                `);
            });
        } else {
             receiptContainer.html('<div class="receipt-item"><span>No item details found.</span></div>');
        }

        confirmBtn.prop('disabled', false).text('Confirm Payment');
    });

    paymentForm.on('submit', function(e) {
        e.preventDefault();
        confirmBtn.prop('disabled', true).text('Processing...');
        
        const formData = $(this).serialize() + '&action=processPayment';

        $.post(ajaxUrl, formData, function(data) {
            // Use the new toast notification instead of alert()
            showToast(data.message, !data.success);
            
            if (data.success) {
                loadUnpaidOrders(); // Reload the queue
            } else {
                confirmBtn.prop('disabled', false).text('Confirm Payment');
            }
        }, 'json');
    });

    // Initial Load
    loadUnpaidOrders();
});
</script>

<?php require_once 'includes/footer.php'; ?>