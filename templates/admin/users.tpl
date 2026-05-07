<section class="container admin-page" style="padding:2em 0;">
   <div class="row">
      <div class="col-12 col-md-3">{nav}</div>
      <div class="col-12 col-md-9">
         <h1>Пользователи ({count})</h1>
         <p class="text-muted">Демо-пароли в документации: <code>demo@homy.local</code> / <code>demo</code>, <code>admin@homy.local</code> / <code>admin</code>.</p>
         <table class="table">
            <thead>
               <tr>
                  <th>ID</th>
                  <th>E-mail</th>
                  <th>Имя</th>
                  <th>Телефон</th>
                  <th>Роль</th>
                  <th class="text-end">Заказов</th>
               </tr>
            </thead>
            <tbody>{rows}</tbody>
         </table>
      </div>
   </div>
</section>
