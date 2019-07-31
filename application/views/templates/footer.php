
<!-- Footer -->
<div class='footer'>
    <div class='container mainFooterHolder'>
        <div class='row'>
            <div class='col-lg-12 in text-center'>
                <div class='col-md-5 col-xs-5 into'>
                    <ul style="margin-right: 100px;">
                        <li><a href="<?php echo base_url(); ?>help/how_it_works">How it works</a></li>
                        <li><a href="<?php echo base_url(); ?>help/pricing">Pricing</a></li>
                        <li><a href="<?php echo base_url(); ?>help">About us</a></li>
                        <li><a href="<?php echo base_url(); ?>help/contact_us">Contact us</a></li>
                        <li><a href="<?php echo base_url(); ?>help/faq">FAQ</a></li>
                        <li><a href="<?php echo base_url(); ?>help/policies">Policies</a></li>
                        <li><a href="<?php echo base_url(); ?>documentation">Documentation</a></li>
                    </ul>
                </div>
                <div class="col-md-7 col-xs-7 videoholder">
                    <h1 style="font-size:50px; color:#ffffff; padding-top:00px; padding-bottom:15px;">
                        <b style="color: white;">About us: </b>
                    </h1>
                    <div class="old-trick">
                        <iframe style="width: 100%;" height="352" src="https://www.youtube.com/embed/NyLfgmhWQ1Q" frameborder="0" allowfullscreen=""></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="footer_main">
    <h3 class="text-center">Copyright &copy; 20<?php echo date('y'); ?> Trackasins.com</h3>
    <h4 class="text-center">Software designed and built by <a href="http://www.expecthuge.com" style="color: #d27842;" target="_blank">Expect Huge</a></h4>
</div>
<?php if(isset($javascript_item) && $javascript_item == "upgrade_plan"){ ?>

<?php } else { ?>
    <script src="<?php echo site_url('assets2/js/jquery.js'); ?>" type="text/javascript"></script>
<?php }?>
<script src="<?php echo site_url('assets2/js/bootstrap.js'); ?>" type="text/javascript"></script>
<script src="https://cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js" type="text/javascript"></script>
<script src="https://cdn.datatables.net/1.10.12/js/dataTables.bootstrap.min.js" type="text/javascript"></script>
<script src="<?php echo site_url('assets2/js/main.js'); ?>" type="text/javascript"></script>
<script type="text/javascript" src="<?php base_url()?>/assets2/js/jquery.fancybox.min.js"></script>
<script src="<?php echo site_url('assets2/global/plugins/bootstrap-sweetalert/sweetalert.js')?>"></script>
<script>
    var base_url = '<?php echo site_url() ?>';
    $(function($) {
        // this script needs to be loaded on every page where an ajax POST may happen
        $.ajaxSetup({
            data: {
                '<?php echo $this->security->get_csrf_token_name(); ?>' : '<?php echo $this->security->get_csrf_hash(); ?>'
            }
        });
    })

    $(document).ready(function(){
        $( ".gn-icon-menu" ).click(
            function() {
                if($(".gn-menu-wrapper").hasClass('gn-open-all')) {
                    $(".gn-menu-wrapper").removeClass('gn-open-all');
                }else{
                    $(".gn-menu-wrapper").addClass('gn-open-all');
                }

            }
        );
        $("#userProfileDropdown").mouseover(function() {
            console.log("show");
            $("li.dropdown").addClass("open");
        });
        $("ul.dropdown-menu, #userProfileDropdown").mouseout(function() {
            if($(".userBox").parents("li.dropdown").eq(0).hasClass("open")){
                    $(".userBox").parents("li.dropdown").eq(0).removeClass("open");
            }
        });
    });

    $(document).mouseup(function(e){
        var container = $(".gn-menu-wrapper");
        if (!container.is(e.target) && container.has(e.target).length === 0)
        {
            if($(".gn-menu-wrapper").hasClass('gn-open-all')) {
                $(".gn-menu-wrapper").removeClass('gn-open-all');
            }
        }
    });

</script>
<?php if(isset($_SESSION['uid'])) {?>
    <script type="text/javascript">
        function checkSession(){
            $.ajax({
                type: 'POST',
                url: '<?php echo site_url() ?>' + "/dashboard/check_session",
                data: {},
                dataType: 'json',
                success: function (response) {
                    if(response.sessionResult == "success"){
                        window.location.href="<?php echo site_url(); ?>";
                    }
                }
            });
        }
        setInterval(function(){
            checkSession();
        }, 1801000);
    </script>

<?php }?>
<?php if(isset($javascript) && $javascript != ""){ ?>
    <script src="<?php echo site_url('assets2/js/pages/'.$javascript.'.js'); ?>" type="text/javascript"></script>
<?php } ?>

</body>
</html>