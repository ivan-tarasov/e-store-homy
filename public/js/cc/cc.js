/*===================================================================================*/
/* Изменение статуса заказа
/*===================================================================================*/
$(document).ready(function(){

   $('[id^=order-status-change-]').on('click', function() {

      var action     = 'change';
      var order_id   = $(this).data('options').order_id;
      var status     = $(this).data('options').status;
      var order_text = $(this).data('options').title;

      var btn_confirm = '5cb85c';
      var btn_error   = 'd9534f';
      var btn_renew   = '5bc0de';
      var input_status = 'text';

      switch (status) {
         case 1:
         case 2:
         case 99:
            input_status = null;
            input_placeholder = null;
            btn_color = btn_confirm;
            break;
         case 3:
            input_status = 'textarea';
            input_placeholder = 'Условия доставки от поставщика';
            btn_color = btn_confirm;
            break;
         case 8:
            //input_status = 'text';
            input_placeholder = 'Причина возобновления заказа';
            btn_color = btn_renew;
            break;
         case 84:
            //input_status = 'text';
            input_placeholder = 'Номер дублируемого заказа';
            btn_color = btn_error;
            break;
         case 83:
            input_status = 'textarea';
            input_placeholder = 'Причина отказа клиента';
            btn_color = btn_error;
            break;
         default:
            //input_status = 'textarea';
            input_placeholder = 'Дополнение к новому статусу';
            btn_color = btn_confirm;
            break;
      }

      swal({
         title: 'Заказ <code>'+order_id+'</code>',
         html: 'Будет произведена смена статуса на<br /><samp><b>&laquo;'+order_text+'&raquo;</b></samp>',
         //type: 'info',
         imageUrl: '/img/cc/order-status/set-2/'+status+'.png',
         imageWidth: 128,
         imageHeight: 128,
         input: input_status,
         inputPlaceholder: input_placeholder,
         allowOutsideClick: true,
         showCancelButton: true,
         showLoaderOnConfirm: true,
         confirmButtonColor: '#'+btn_color,
         confirmButtonText: '<i class="fa fa-lg fa-check" aria-hidden="true"></i> Сменить статус',
         cancelButtonText: '<i class="fa fa-times" aria-hidden="true"></i> Отмена'
      }).then(
         function(value) {
            $.post('/class/ajax/order.ajax.php', { action:action,order_id:order_id,status:status,note:value }, function(data) {
               var response = jQuery.parseJSON(data);

               if(!response.error) {
                  swal({
                     title: 'Статус изменен!',
                     type: 'success',
                     timer: 600,
                     showConfirmButton: false,
                     onClose: function() { window.location.reload(); }
                  }).done();
               } else {
                  swal({
                     title: 'Произошла ошибка',
                     text: response.error,
                     type: 'error',
                     confirmButtonColor: '#dd6b55',
                     confirmButtonText: 'Закрыть'
                  }).done();
               }

            }).fail(function() {
               alert( "Обновление не удалось" );
            });

            return false;
         }
      ).done();/**/

      return false;
   });

});

/*===================================================================================*/
/* Удаление заказа
/*===================================================================================*/
$(document).ready(function(){

   $('[id^=order-delete-]').on('click', function() {

      var action     = 'delete';
      var order_id   = $(this).data('options').order_id;

      swal({
         title: 'Удаление заказ',
         text: 'Заказ <code>'+order_id+'</code>\nбудет перемещен в корзину.',
         imageUrl: '/img/cc/order-status/set-2/trash-bin-128.png',
         imageWidth: 128,
         imageHeight: 128,
         //input: 'textarea',
         inputPlaceholder: 'Укажите причину удаления заказа',
         allowOutsideClick: true,
         showCancelButton: true,
         showLoaderOnConfirm: true,
         confirmButtonColor: "#dd6b55",
         confirmButtonText: "Да, удалить заказ",
         cancelButtonText: "Нет, не удалять",
         preConfirm: false
      }).then(function() {
         $.post('/class/ajax/order.ajax.php', { action:action,order_id:order_id }, function(data) {
            var response = jQuery.parseJSON(data);

            if(!response.error) {
               swal({
                  title: 'Заказ успешно удален!',
                  type: 'success',
                  timer: 600,
                  showConfirmButton: false,
                  onClose: function() { window.location.reload(); }
               }).done();
            } else {
               swal({
                  title: 'Произошла ошибка',
                  text: response.error,
                  type: 'error',
                  confirmButtonColor: '#dd6b55',
                  confirmButtonText: 'Закрыть'
               }).done();
            }

         }).fail(function() { alert( "Обновление не удалось" ); });

         return false;
      }).done();

      return false;
   });

});                   
