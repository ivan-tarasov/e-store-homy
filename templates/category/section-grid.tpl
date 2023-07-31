<section id="products-grid">
   <div class="grid-list-products">
      <h2 class="section-title">{cat_name}{sort_brand}</h2>
      <div class="space20"></div>

      <div class="control-bar">
         <script>
         $(function(ready) {
            $( "#item-sort" ).change(function() {
               this.form.submit();
            });
         });
         </script>

         <!--form class="le-select" id="items-show" name="items-show" method="post">
            <label for="item-count">Позиций:</label>
            <select id="item-count" name="item-count">
               <option value="9" selected>9</option>
               <option value="36">36</option>
               <option value="72">72</option>
            </select>
         </form-->

         <form id="sorting" class="le-select" name="sorting" method="post">
            <label for="item-sort">Сортировка:</label>
            <select id="item-sort" name="item-sort">

               {item-sort}

            </select>
         </form>

         <div class="grid-list-buttons">
            <ul>
               <!--li class="grid-list-button-item">
                  <a data-toggle="tab" href="#grid-view">
                     <i class="fa fa-th-large"></i> Сетка
                  </a>
               </li-->
               <li class="grid-list-button-item active">
                  <a data-toggle="tab" href="#list-view">
                     <i class="fa fa-th-list"></i> Список
                  </a>
               </li>
             </ul>
         </div>
      </div>

      <div class="tab-content">

         <div id="grid-view" class="products-grid fade tab-pane">
            <div class="product-grid-holder">
               <div class="row no-margin">


                  {product_grid}

               </div>
            </div>

         </div>

         <div id="list-view" class="products-grid fade tab-pane in active">
            <div class="products-list">

               {product_list}

            </div>

         </div>

         <div class="pagination-holder">
            <div class="row">

               <div class="col-xs-12 col-sm-9 text-left">
                  {pagination}
               </div>

               <div class="col-xs-12 col-sm-3">
                  <div class="result-counter">
                     <span>Показано <b>{positions_from}&ndash;{positions_to}</b></span> из <span><b>{positions_total}</b></span> позиций
                  </div>
               </div>

            </div>
         </div>

      </div>
   </div>

</section>
