  </div><!-- main-content -->
</div><!-- main-wrap -->
<script>
// Close sidebar on outside click (mobile)
document.addEventListener('click', function(e){
  var sb = document.getElementById('sidebar');
  if(sb && sb.classList.contains('open') && !sb.contains(e.target) && !e.target.closest('.menu-toggle')){
    sb.classList.remove('open');
  }
});
</script>
</body>
</html>
