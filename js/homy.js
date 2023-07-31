function scrollToAnchor(aid){
    var aTag = $("a[name='"+ aid +"']");
    $('html,body').animate({scrollTop: aTag.offset().top},'slow');
}

$(document).ready( function() {
   $('#hero').carousel();

   var clickEvent = false;

   $('#hero').on('click', '.nav a', function() {
      clickEvent = true;
      $('.nav li').removeClass('active');
      $(this).parent().addClass('active');
   }).on('slid.bs.carousel', function(e) {
      if (!clickEvent) {
         var count = $('#hero .nav').children().length -1;
         var current = $('#hero .nav li.active');
         current.removeClass('active').next().addClass('active');
         var id = parseInt(current.data('slide-to'));
         if (count == id) {
            $('#hero .nav li').first().addClass('active');
         }
      }
      clickEvent = false;
   });
});

/*===================================================================================*/
/*  Маска ввода номера телефона
/*===================================================================================*/
$(document).ready(function(){
   $( "#reg-phone, #buy-clc-phone" ).mask("+7 (999) 999-9999",{placeholder:"*"});
});

/*===================================================================================*/
/*  Поиск по сайту
/*===================================================================================*/
$(document).ready(function() {

   $("#search-form").autocomplete({
      source: "/class/ajax/search.ajax.php",
      minLength: 2,
      select: function(event, ui) {
         var getUrl = ui.item.id;
         if (getUrl != '#') {
            location.href = getUrl;
         }
      },
      html: true,
      open: function(event, ui) {
         $(".ui-autocomplete").css("z-index", 1000);
      },
   });

});


/*===================================================================================*/
/*  Регистрационная форма
/*===================================================================================*/
$(document).ready(function(){

   $( '#reg-submit' ).on('click', function() {

      var action = "reg";
      var phone = $('#reg-phone').val();

      $( '#reg-phone' ).attr("disabled", true);
      $(this).attr("disabled", true).html("Отправляю <i class=\"fa fa-lg fa-pulse fa-spinner\"></i>").addClass('btn');

      $.post('/class/ajax/auth.ajax.php', { action:action, phone:phone }, function(data) {
         var response = jQuery.parseJSON(data);
         $( '#reg-response' ).html(response.message);

         if(response.ok == 1) {
            $( '#reg-form' ).hide('slow');
         } else {
            $( '#reg-phone, #reg-submit' ).removeAttr("disabled");
            $( '#reg-submit' ).html('Зарегистрироваться').removeClass('btn');
         }

      }).fail(function() {
         alert( "Обновление не удалось" );
      });

      return false;

   });

});

/*===================================================================================*/
/*  Форма авторизации
/*===================================================================================*/
$(document).ready(function(){

   $( '#auth-form' ).submit(function() {

      var action = "auth";
      var login = $('#auth-login').val();
      var pass = $('#auth-pass').val();

      $( '#auth-login, #auth-pass, #auth-submit' ).attr("disabled", true);
      $( '#auth-submit' ).html("Вхожу <i class=\"fa fa-lg fa-pulse fa-spinner\"></i>").addClass('btn');

      $.post('/class/ajax/auth.ajax.php', { action:action, login:login, pass:pass }, function(data) {
         var response = jQuery.parseJSON(data);
         $( '#auth-response' ).html(response.message);

         if(response.ok == 1) {
            window.location.replace("/my/")
         } else {
            $( '#auth-form' )[0].reset();
            $( '#auth-login, #auth-pass, #auth-submit' ).removeAttr("disabled");
            $( '#auth-submit' ).html('Войти').removeClass('btn');
         }

      }).fail(function() {
         alert( "Обновление не удалось" );
      });

      return false;

   });

});

/*===================================================================================*/
/*  Смена пароля пользователя
/*===================================================================================*/
$(document).ready(function(){

   $( '#chenge-password' ).submit(function() {

      var action = "chpass";
      var oldpass = $('#oldpass').val();
      var newpass = $('#newpass').val();
      var passchk = $('#passchk').val();

      $( '#oldpass, #newpass, #passchk, #changepass-submit' ).attr("disabled", true);
      $( '#changepass-submit' ).html("Проверяю <i class=\"fa fa-lg fa-pulse fa-spinner\"></i>").addClass('btn');

      $.post('/class/ajax/auth.ajax.php', { action:action, oldpass:oldpass, newpass:newpass, passchk:passchk }, function(data) {
         var response = jQuery.parseJSON(data);
         $( '#changepass-response' ).html(response.message);

         if(response.ok == 1) {
            swal({
               title: 'Пароль изменен',
               text: 'Ваш пароль был успешно изменен.',
               type: 'success',
               confirmButtonText: 'ОК',
               preConfirm: false,
               onClose: function() { window.location.href = "/my/"; }
            }).done();
         } else {
            $( '#chenge-password' )[0].reset();
            $( '#oldpass, #newpass, #passchk, #changepass-submit' ).removeAttr("disabled");
            $( '#changepass-submit' ).html('Сменить пароль').removeClass('btn');
         }

      }).fail(function() {
         alert( "Обновление не удалось" );
      });

      return false;

   });

});

/*===================================================================================*/
/*  Форма обратной связи
/*===================================================================================*/
$(document).ready(function(){

   $( '#contact-form' ).submit(function() {

      var category = $('input[name=contact-category]:checked').val();
      var name = $('#contact-name').val();
      var email = $('#contact-email').val();
      var subject = $('#contact-subject').val();
      var message = $('#contact-message').val();

      $( '#contact-submit' ).attr("disabled", true);
      $( '#contact-submit' ).html("Отправляю <i class=\"fa fa-lg fa-pulse fa-spinner\"></i>").addClass('btn');

      $.post('/class/ajax/feedback.ajax.php', { category:category, name:name, email:email, subject:subject, message:message }, function(data) {
         var response = jQuery.parseJSON(data);
         /*$( '#contact-response' ).html(response.message);*/

         if(response.ok == 1) {
            mes_title = 'Спасибо за Ваш отзыв!';
            mes_text = 'Если в Вашем сообщение был вопрос, который требует ответа, то мы свяжемся с Вами по указанному e-mail в ближайшее время.';
            mes_type = 'success';
            mes_confirm = '5cb85c';
         } else {
            mes_title = 'Ошибка';
            mes_text = 'При отправке отзыва возникла ошибка. Попробуйте еще раз.';
            mes_type = 'error';
            mes_confirm = '5cb85c';
         }

         swal({
            title: mes_title,
            text: mes_text,
            type: mes_type,
            confirmButtonColor: '#'.mes_confirm,
            confirmButtonText: 'Закрыть',
            preConfirm: false
         }).then(function() {
            $( '#contact-form' )[0].reset();
            $( '#contact-submit' ).removeAttr("disabled");
            $( '#contact-submit' ).html('Отправить сообщение').removeClass('btn');
         }).done();

      }).fail(function() {
         alert( "Обновление не удалось" );
      });

      return false;

   });

});

/*===================================================================================*/
/*  Выход из аккаунта
/*===================================================================================*/
$(document).ready(function(){

   $('[id^=logout]').on('click', function() {
      swal({
         title: 'Выйти из аккаунта?',
         text: 'Вы уверены, что хотите выйти из своего аккаунта?',
         type: "warning",
         showCancelButton: true,
         confirmButtonColor: "#DD6B55",
         confirmButtonText: "Да, я уверен",
         cancelButtonText: "Я передумал",
         preConfirm: false
      }).then(function() {
         var action = "out";

         $.post('/class/ajax/auth.ajax.php', { action:action }, function(data) {
            var response = jQuery.parseJSON(data);
            if(response.out == 1) {
               window.location.replace("/")
            } else {
               console.log('May the force be with you! Somthing wrong...')
            }
         }).fail(function() {
            alert( "Обновление не удалось" );
         });

      }).done();

   });

});

/*===================================================================================*/
/*  Просмотр фото товара
/*===================================================================================*/
$(document).ready(function(){

   $(".group1").colorbox({rel:'group1', transition:"none", width:"100%", height:"100%", opacity: 1});

   $(document).bind('cbox_open', function() {
      $('html').css({ overflow: 'hidden' });
   }).bind('cbox_closed', function() {
      $('html').css({ overflow: '' });
   });

});/**/

/*===================================================================================*/
/*  Добавление товара в корзину
/*===================================================================================*/
$(document).ready(function(){

   $('[id^=addtocart-]').on('click', function() {

      var action		= $(this).data('options').action;
      var id			= $(this).data('options').id;
		var name			= $(this).data('options').name;
		var price		= $(this).data('options').price;
		var brand		= $(this).data('options').brand;
		var category	= $(this).data('options').category;

      $(this).attr("disabled", true).html("Переношу <i class=\"fa fa-lg fa-pulse fa-spinner\"></i>");

      $.post('/class/ajax/shoppingcart.ajax.php', { action: action, id: id }, function(data) {
         var response = jQuery.parseJSON(data);
         $( '#total-qty' ).html(response.totalqty);
         $( '#sub-total' ).html(response.subtotal);
			$( '#addtocart-' + id )
				.removeAttr('id data-options')
				.attr("disabled", false)
				.attr('onClick', "javascript:window.location.href='/cart/'; return false;")
				.addClass("incart")
				.html("В корзине");
			/*$( '#addtocart-' + id ).replaceWith(function(){
				$a = $("<a>", {html: $(this).html()});
				$.each(this.attributes, function(i, attribute){
					$a.attr(attribute.name, attribute.value);
				});
				return $a;
			});/**/
			//$( '#addtocart-' + id ).attr('onClick', "javascript:window.location.href='/cart/'; return false;");
			//$( '#addtocart-' + id ).removeAttr('id').removeAttr('data-options');

         if (response.cartup_flg == false) {
            $( '#cartUp' ).html(response.cartup);
         } else {
            $( '#cartUp' ).append(response.cartup);
				//incartReplace();
				window.dataLayer.push({"ecommerce":{"add":{"products":[{"id":id,"name":name,"price":price,"brand":brand,"category":category}]}}});
         }

      }).fail(function() {
         alert( "Обновление не удалось" );
      });

      return false;

   });

});

/*===================================================================================*/
/*  Оформление заказа
/*===================================================================================*/
$(document).ready(function(){

   $( '#checkout-form' ).submit(function() {

      $( '#checkout-submit' ).attr("disabled", true);
      $( '#checkout-submit' ).html("Оформляю заказ <i class=\"fa fa-lg fa-pulse fa-spinner\"></i>").addClass('btn');

      $.post('/class/ajax/checkout.ajax.php', $(this).serialize(), function(data) {
         var response = jQuery.parseJSON(data);

         $( 'div, input' ).removeClass('has-error has-error-border');
         $('[id$="-help"]').html('');

         if (typeof response.error == 'undefined') {

				var push = JSON.parse(response.push);
				window.dataLayer.push(push);

            switch (response.class) {
               case 'success':
                  var btncolor = '5cb85c';
                  var btntext = 'Перейти к заказу';
                  break;
               case 'error':
                  var btncolor = 'dd6b55';
                  var btntext = 'Закрыть';
                  break;
            }

            swal({
               title: response.title,
               text: response.message,
               type: response.class,
               confirmButtonColor: '#' + btncolor,
               confirmButtonText: btntext,
               preConfirm: false,
               onClose: function() { window.location.href = response.url; }
            }).done();

         } else {
            scrollToAnchor('checkout-form');
            $( '#' + response.error.input ).parent().addClass( 'has-error' );
            $( '#' + response.error.input ).addClass( 'has-error-border' );
            $( '#' + response.error.input + '-help' ).html( response.error.help );

            $( '#checkout-submit' ).removeAttr("disabled");
            $( '#checkout-submit' ).html('Оформить заказ').removeClass('btn');
         }
      }).fail(function() {
         alert( "Обновление не удалось" );
      });

      return false;

   });

});

/*===================================================================================*/
/*  Покупка в 1 клик
/*===================================================================================*/
$(document).ready(function(){

   $( '#buy-clc-form' ).submit(function() {
   //$( '[id^=buy-clc-form]' ).submit(function() {

      var btnClone = $("#buy-clc-submit").clone();
      var recaptcha  = $('#g-recaptcha-response').val();
      var id       = $('#buy-clc-id').val();
      var name     = $('#buy-clc-name').val();
      var phone    = $('#buy-clc-phone').val();
      var message  = $('#buy-clc-message').val();

      $( '#buy-clc-form :input' ).attr("disabled", true);
      $( '#buy-clc-submit' ).html("Отправляю <i class=\"fa fa-lg fa-pulse fa-spinner\"></i>").addClass('btn');

      $.post('/class/ajax/buy1clc.ajax.php', { captcha:recaptcha, id:id, name:name, phone:phone, message:message }, function(data) {
         var response = jQuery.parseJSON(data);

         $('#buy-clc-form :input').removeAttr("disabled");
         $("#buy-clc-submit").replaceWith(btnClone);

         if (response.ok) {
				var push = JSON.parse(response.push);
				window.dataLayer.push(push);

            mes_title = response.title;
            mes_text = response.ok;
            mes_type = 'success';
            mes_confirm = '5cb85c';
         } else {
            mes_title = response.title;
            mes_text = response.error;
            mes_type = 'error';
            mes_confirm = '5cb85c';
         }

         swal({
            title: mes_title,
            text: mes_text,
            type: mes_type,
            confirmButtonColor: '#'.mes_confirm,
            confirmButtonText: 'Закрыть'
            //preConfirm: true,
         }).then(function() {
            if (mes_type == 'success') {
               $( '#buy-clc-form' )[0].reset();
               $('#buy-clc').modal('hide');
               if (id == 0) {
                  window.location.replace("/")
               }
            }
         }).done();

      }).fail(function() {
         alert( "Обновление не удалось" );
      });/**/

      return false;

   });

});

/*===================================================================================*/
/*  Scroll to top кнопка
/*===================================================================================*/
$(document).ready(function(){

   $( "#back-top" ).css("display", "none");

   $(window).scroll(function(){
      if($(window).scrollTop() > 400){
         $( "#back-top" ).fadeIn();
      } else {
         $( "#back-top" ).fadeOut();
      }
   });

   $("#back-top").click(function(){

      // Disable the default behaviour when a user clicks an empty anchor link.
      // (The page jumps to the top instead of // animating)
      event.preventDefault();

      // Animate the scrolling motion.
      $("html, body").animate({
         scrollTop:0
      },"slow");

   });

});

$(document).ready(function(){

   $('[id^=popover]')
      .mouseover(function() {
         $(this).popover( 'show' );
      })
      .mouseout(function() {
         $(this).popover( 'hide' );
      });
});

/*===================================================================================*/
/*  Загрузка аватара пользователя
/*===================================================================================*/
$(document).ready(function(){

   $('[id^=avatar]').on('click', function() {
      var avatar = $(this).data('avatar');

      swal({
         title: 'Сменить аватар',
         text: 'Вы действительно хотите сменить свой текущий аватар пользователя?',
         imageUrl: avatar,
         imageWidth: 128,
         imageHeight: 128,
         showCancelButton: true,
         confirmButtonColor: "#5cb85c",
         confirmButtonText: "Да, хочу!",
         cancelButtonText: "Я передумал",
         preConfirm: false,
      }).then(function() {
         swal({
            title: 'Новый аватар',
            html: '<form action="/class/ajax/avatar-upload.php" method="post" enctype="multipart/form-data" id="avatar-form"> <span class="btn btn-success btn-lg btn-file">Открыть<input name="image_file" id="imageInput" type="file" /></span><img src="/img/AjaxLoader.gif" id="loading-img" style="display:none;" alt="Пожалуйста, подождите..."/></form>',
            imageUrl: avatar,
            imageWidth: 128,
            imageHeight: 128,
            confirmButtonText: "Я передумал",
            confirmButtonColor: "#d0d0d0"
         }).done();

         var options = {
            target: '#avatar-form',      // элемент, который будет обновлен при ответе сервера
            beforeSubmit: beforeSubmit,  // функция перед отправкой формы
            success: afterSuccess,       // функция после отправки формы
            resetForm: true              // обнуляем форму после удачного submit
         };

         $('input[type=file]').change(function() {
            $('#avatar-form').submit();
         });

         $('#avatar-form').submit(function() {
            $(this).ajaxSubmit(options);
            // всегда возвращаем false, чтобы предотвратить отправку формы стандартными методами
            return false;
         });

         function afterSuccess() {
            $('#loading-img').hide();  // прячем иконку загрузки
            swal({
               title: 'Аватар загружен!',
               type: 'success',
               onClose: function() { window.location.href = "/my/"; }
            }).done();
         }

         // функция для проверки размера перед отправкой
         function beforeSubmit() {
            // проверяем поддерживает браузер все функции или нет
            if (window.File && window.FileReader && window.FileList && window.Blob) {
               if( !$('#imageInput').val()) { // проверяем пустое поле загрузки файла
                  $("#avatar-form").html("Вы забыли выбрать аватар для загрузки...");
                  return false
               }

               var fsize = $('#imageInput')[0].files[0].size; // получаем размер файла
               var ftype = $('#imageInput')[0].files[0].type; // получаем тип файла

               // разрешаем только валидные типы файлов изображений
               switch(ftype) {
                  case 'image/png': case 'image/gif': case 'image/jpeg': case 'image/pjpeg':
                     break;
                  default:
                     $("#avatar-form").html("<b>"+ftype+"</b> не поддерживаемый тип файла!");
                     return false
               }

               // Разрешаем, если размер файла меньше 1Мб (1048576)
               if (fsize>1048576) {
                  $("#avatar-form").html("<b>"+bytesToSize(fsize) +"</b> Слишком большой размер файла! <br />Попробуйте уменьшить размер в одном из графических редакторов.");
                  return false
               }

               $('#loading-img').show(); // показываем иконку загрузки
               $("#avatar-form").html("");
            } else {
               // Выводим сообщение для старых браузеров, не поддерживающих HTML5
               $("#avatar-form").html("Пожалуйста, обновите Ваш браузер. Это поможет избежать проблем в будущем.");
               return false;
            }
         }

         // функция для конвертации байтов
         function bytesToSize(bytes) {
            var sizes = ['байт', 'Кб', 'Мб', 'Гб', 'Тб'];
            if (bytes == 0)
               return '0 байт';
            var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
            return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i];
         }

      }).done();

   });

});
