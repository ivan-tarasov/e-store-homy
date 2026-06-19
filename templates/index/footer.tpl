   <footer id="footer" class="color-bg">

      <div class="link-list-row">
         <div class="container">
            <div class="row">

            <div class="col-12 col-md-4">
               <div class="contact-info">
                  <p class="regular-bold">{addr_descr}</p>
                  <p>
                     {homy_address}<br />
                     {homy_phone}
                  </p>
               </div>
            </div>

            <div class="col-12 col-md-3">
               <div class="link-widget">
                  <div class="widget">
                     <h3>{prod_catalog}</h3>
                     <ul>
                        {quick_menu}
                     </ul>
                  </div>
               </div>
            </div>

            <div class="col-12 col-md-3">
               <div class="link-widget">
                  <div class="widget">
                     <h3>{{footer.services}}</h3>
                     <ul>
                        <li><a href="/about/">{{footer.about}}</a></li>
                        <li><a href="/terms/">{{footer.delivery}}</a></li>
                        <li><a href="/feedback/">{{footer.feedback}}</a></li>
                        <li><a href="/credits/">{{footer.credits}}</a></li>
                     </ul>
                  </div>
               </div>
            </div>

            <div class="col-12 col-md-2">
               <div class="link-widget">
                  <div class="widget">
                     <h3>{{footer.account}}</h3>
                     <ul>
                        <li><a href="/login/">{{footer.account_login}}</a></li>
                        <li><a href="/my/">{{footer.account_cabinet}}</a></li>
                     </ul>
                  </div>
               </div>
            </div>

            </div><!-- /.row -->
         </div>
      </div>

      <div class="copyright-bar">
         <div class="container">
            <div class="row">
               <div class="col-12">
                  <small>{oferta}</small>
               </div>
               <div class="col-12">
                  <div class="copyright">
                     {cp_year} &copy; <a href="/">Homy demo</a> — {{footer.tagline}}
                     {admin_inf}
                  </div>
               </div>
            </div><!-- /.row -->
         </div>
      </div>

   </footer>

</div>

<script src="/js/bootstrap.min.js"></script>
<script src="/js/swiper-bundle.min.js"></script>
<script src="/js/homy.js"></script>
<script src="/js/homy.gallery.js"></script>
<script src="/js/homy.search.js"></script>

</body>
</html>
