<main id="authentication" class="inner-bottom-md">
   <div class="container">
      <div class="row">

         <div class="col-md-6">
            <section class="section sign-in inner-right-xs">
               <h2 class="bordered">Авторизация на сайте</h2>

               <div class="space20"></div>
               <div class="row">

                  {extauth}

               </div>
               <div class="space40"></div>

               <span id="auth-response"></span>

               <form role="form" class="login-form cf-style-1" id="auth-form">
                  <div class="field-row">
                     <label>Телефонный номер (без 8-ки) или электронная почта</label>
                     <input type="text" id="auth-login" class="le-input">
                  </div>

                  <div class="field-row">
                     <label>Пароль</label>
                     <input type="password" id="auth-pass" class="le-input">
                  </div>

                  <div class="field-row clearfix">
                     <span class="pull-left">
                        <label class="content-color">
                           <input type="checkbox" name="rememberme" class="le-checbox auto-width inline">
                           <span class="bold">Помнить меня</span>
                        </label>
                     </span>
                     <span class="pull-right">
                        <a href="#" class="content-color bold">Забыли пароль?</a>
                     </span>
                  </div>

                  <div class="buttons-holder">
                     <button type="submit" class="le-button huge" id="auth-submit">Войти</button>
                  </div>
               </form>

            </section>
         </div>

         <div class="col-md-6">
            <section class="section register inner-left-xs">
               <h2 class="bordered">Регистрация нового пользователя</h2>
               <p>Создайте свой аккаунт для покупок прямо сейчас. Регистрация проходит в 1 клик.</p>
               <p>После регистрации на указанный Вами номер телефона придет смс-сообщение с данными для входа в личный кабинет.</p>
               <p id="reg-response"></p>

               <form role="form" class="register-form cf-style-1" id="reg-form">
                  <div class="field-row">
                     <label>Номер телефона</label>
                     <input type="text" id="reg-phone" name="phone" class="le-input input-xxl form-control bg-success">
                  </div>

                  <div class="buttons-holder">
                     <button type="submit" class="le-button huge" id="reg-submit">Зарегистрироваться</button>
                  </div>

                  <hr />
                  {terms-personal}

               </form>

               <h2 class="semi-bold">Зарегистрируйтесь сегодня и Вы сможете:</h2>

               <ul class="list-unstyled list-benefits">
                  <li><i class="fa fa-check primary-color"></i> Ускорить заказ товаров</li>
                  <li><i class="fa fa-check primary-color"></i> Следить за своими заказами</li>
                  <li><i class="fa fa-check primary-color"></i> Хранить историю своих заказов</li>
                  <li><i class="fa fa-check primary-color"></i> И еще очень многое...</li>
               </ul>

            </section>

         </div>

      </div>
   </div>
</main>
