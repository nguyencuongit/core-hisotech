// // ======================
// // GLOBAL STATE
// // ======================
// window.shippingFee = 0;

// // ======================
// // UPDATE SHIPPING UI (MAIN)
// // ======================
// function updateShippingMethodUI() {
//     let fee = parseInt(window.shippingFee || 0);

//     // lấy tất cả shipping method
//     $('.shipping_method_input').each(function () {
//         let input = $(this);
//         let li = input.closest('li');
//         let span = li.find('label span');

//         if (!span.length) return;

//         // lấy text gốc (loại bỏ strong)
//         let baseText = span.clone()
//             .children()
//             .remove()
//             .end()
//             .text()
//             .trim()
//             .replace(/-.*$/, '') // bỏ phần cũ sau dấu "-"
//             .trim();

//         // update text
//         span.text(`${baseText} - ${fee}₫`);

//         // update value
//         // input.val(fee);
//     });
// }

// // ======================
// // UPDATE TOTAL
// // ======================
// function updateTotal() {
//     let subtotal = parseInt($('#subtotal').data('value')) || 0;
//     let fee = parseInt(window.shippingFee || 0);

//     let total = subtotal + fee;

//     $('#total').text(total + ' VND');
// }

// // ======================
// // CALL API
// // ======================
// function handleShippingChange() {

//     let data = {
//         province_id: $('#address_state').val(),
//         district_id: $('#address_city').val(),
//     };

//     $.post('/ajax/shipping-fee-checkout', data, function (res) {

//         window.shippingFee = parseInt(res.fee || 0);

//         updateShippingMethodUI();
//         updateTotal();

//         $('input[name=shipping_fee]').val(window.shippingFee);
//     });
// }

// // ======================
// // EVENT
// // ======================
// $(document).on('change', '#address_city', function () {
//     handleShippingChange();
// });

// // ======================
// // INIT
// // ======================
// $(document).ready(function () {
//     updateShippingMethodUI();
//     updateTotal();
// });

// // ======================
// // QUAN TRỌNG NHẤT 🔥
// // ======================
// $(document).ajaxComplete(function () {
//     // Botble render lại → update lại UI
//     updateShippingMethodUI();
//     updateTotal();
// });