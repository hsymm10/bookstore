# Task List — BookStore Comprehensive Fix

## A. Database Migrations
- [x] Migration: add payment fields to orders table
- [x] Migration: add status to chats table
- [x] Run migrations

## B. Backend — Models
- [x] Model Order.php — fillable, casts, generateInvoiceNumber
- [x] Model Chat.php — status fillable/cast

## C. Backend — Controllers
- [x] OrderController — store() with invoice_number, QR, barcode
- [x] OrderController — confirm() save payment data to DB
- [x] OrderController — getByInvoice() new method
- [x] AdminChatController — fix timestamps, read status
- [x] UserChatController — fix timestamps, read status

## D. Backend — Resources
- [x] OrderResource — add payment fields
- [x] ChatResource — add status, relative time WIB

## E. Backend — Routes
- [x] Add GET /orders/invoice/{invoiceNumber}
- [x] Add POST /orders/{order}/pay (payment endpoint)

## F. Frontend — Pages
- [x] keranjang.vue — auto clear cart, redirect to invoice
- [x] pages/invoice/[invoice_number].vue — NEW full invoice digital page
- [x] user/riwayat.vue — full order history with all fields
- [x] admin/kasir.vue — proper payment flow, struk format, print guard
- [x] user/chat.vue — status pesan, WIB timestamp
- [x] admin/chat.vue — status pesan, WIB timestamp relative
