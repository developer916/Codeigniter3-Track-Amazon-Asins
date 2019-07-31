<div class='mainCont'>
    <div class="settingsHeadline headline headline-site-color">
        <div class="container-fluid inner">
            <div class="topHeadline container text-center">
                <h3>Help & Support</h3>
            </div>
        </div>
    </div>
    <div class="mainSettingsCont">
        <div class="container mainSettingCont">
            <?php include('left_grid.php');?>
            <div class="settingsMainCont col-lg-9 col-sm-12 pull-right">
                <div class="innerCont card card-default clearfix">
                    <div class="topHeader col-lg-12">
                        <h3>Pricing</h3>
                        <h3 class="right-corner">
                            <p>Monthly Total: $</p><p id="monthly-total">0</p>
                        </h3>
                    </div>
                    <div class="bottomContent col-lg-12">
                        <div class="changePasswordHolder">
                            <?php /*echo form_open('settings/upgrade_plan_process', array('id' => 'settings_upgrade_plan', 'action' => ''));*/ ?>
                            <form>
                                <div class='boxesSelectHolder clearfix'>
                                    <div class="box priceBox col-lg-6">
                                        <div class="innerBox">
                                            <div class="topHead">
                                                <h3>Track ASINS</h3>
                                            </div>
                                            <div class="content">
                                                <div class="inputType">
                                                    <select class="from-control" onchange="onGetTotal()" id="asins-monthly" name="track_support_id" open>
                                                        <?php if(count($track_supports)>0) {
                                                            foreach($track_supports as $key => $track_support){?>
                                                                <?php if($track_support->price ==  99999) {?>
                                                                    <option value="<?php echo $track_support->id;?>" <?php if(isset($support) && ($support->track_support == $track_support->id)){?> selected <?php }?>><?php echo $track_support->description;?></option>
                                                                <?php } else {?>
                                                                    <option value="<?php echo $track_support->id;?>" <?php if(isset($support) && ($support->track_support == $track_support->id)){?> selected <?php }?>><?php echo $track_support->description." $".$track_support->price."/Month"; ?></option>
                                                                <?php } ?>
                                                            <?php } }?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="box priceBox col-lg-6">
                                        <div class="innerBox">
                                            <div class="topHead">
                                                <h3>Email Support</h3>
                                            </div>
                                            <div class="content">
                                                <div class="inputType">
                                                    <select class="from-control" onchange="onGetTotal()" id="email-monthly" name="email_support_id">
                                                        <?php if(count($email_supports) >0) {
                                                            foreach($email_supports as $key => $email_support) {?>
                                                                <?php if($email_support->price != 0) {?>
                                                                    <option value="<?php echo $email_support->id ?>" <?php if(isset($support) && ($support->email_support == $email_support->id)){?> selected <?php }?>><?php echo $email_support->description." : $". $email_support->price."/Month" ?></option>
                                                                <?php } else {?>
                                                                    <option value="<?php echo $email_support->id ?>" <?php if(isset($support) && ($support->email_support == $email_support->id)){?> selected <?php }?>><?php echo $email_support->description ?></option>
                                                                <?php }?>
                                                            <?php } }?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
<!--                                    <div class="box priceBox col-lg-6 col-lg-offset-6">-->
<!--                                        <div class="innerBox">-->
<!--                                            <div class="topHead">-->
<!--                                                <h3>Live Chat Support</h3>-->
<!--                                            </div>-->
<!--                                            <div class="content">-->
<!--                                                <div class="inputType">-->
<!--                                                    <select class="from-control" onchange="onGetTotal()" id="live-monthly">-->
<!--                                                        <option value="0">Nothing selected</option>-->
<!--                                                        <option value="50">Live Chat:$50/Month (9AM to 5PM EST)</option>-->
<!--                                                        <option value="200">Live Chat:$200/Month (24/7)</option>-->
<!--                                                    </select>-->
<!--                                                </div>-->
<!--                                            </div>-->
<!--                                        </div>-->
<!--                                    </div>-->
<!---->
<!--                                    <div class="box priceBox col-lg-6 col-lg-offset-6">-->
<!--                                        <div class="innerBox">-->
<!--                                            <div class="topHead">-->
<!--                                                <h3>Phone Support</h3>-->
<!--                                            </div>-->
<!--                                            <div class="content">-->
<!--                                                <div class="inputType">-->
<!--                                                    <select class="from-control" onchange="onGetTotal()" id="phone-monthly">-->
<!--                                                        <option value="0">Nothing selected</option>-->
<!--                                                        <option value="100">Phone Support: $100/Month (9AM to 5PM EST)</option>-->
<!--                                                        <option value="300">Phone Support: $300/Month (24/7)</option>-->
<!--                                                    </select>-->
<!--                                                </div>-->
<!--                                            </div>-->
<!--                                        </div>-->
<!--                                    </div>-->
                                </div>
                                <div class="bottomProfilePicForm col-lg-12" style="text-align: center;">
                                    <?php if(isset($_SESSION['user_id'])){ ?>
                                        <a href="<?php echo base_url()?>settings/membership_account" class="btn btn-embossed btn-success primarycolorbtn">Membership & Account </a>
                                    <?php } else { ?>
                                    <a href="<?php echo base_url()?>" class="btn btn-embossed btn-success primarycolorbtn">Sign me up!</a>
                                    <?php } ?>
                                </div>
                            </form>
                                <?php /*echo '</form>';*/ ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?php base_url()?>/assets2/js/jquery.js" type="text/javascript"></script>
<script type="text/javascript">
//    function onGetTotal(){
//        var total = 0;
//        if($("#asins-monthly").val() !='') {
//            total = total+ $("#asins-monthly").val() * 1;
//        }
//        if($("#live-monthly").val() !='') {
//            total = total+ $("#live-monthly").val() * 1;
//        }
//        if($("#email-monthly").val() !='') {
//            total = total+ $("#email-monthly").val() * 1;
//        }
//        if($("#phone-monthly").val() !='') {
//            total = total+ $("#phone-monthly").val() * 1;
//        }
//        $("#monthly-total").text(total);
//    }
$(document).ready(function() {
    onGetTotal();
});
function onGetTotal(){
    var total = 0;
    var asins_monthly ="";
    var email_monthly ="";
    if($("#asins-monthly").val() !='') {
        asins_monthly = $("#asins-monthly").val();
    }
    if($("#email-monthly").val() !='') {
        email_monthly = $("#email-monthly").val();
    }

    $.ajax({
        url: '<?php echo site_url("/help/get_total_value")?>',
        data: {'track_support_id': asins_monthly, 'email_support_id': email_monthly},
        method: 'POST',
        dataType: 'json'
    }).success(function (response) {
        if (response.status == 'success') {
            $("#monthly-total").text(response.total);
        }
    });
}
</script>