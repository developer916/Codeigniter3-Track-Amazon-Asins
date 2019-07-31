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
                        <h3>Contact Us</h3>
                    </div>
                    <div class="bottomContent col-lg-12" style='padding-left: 0px;'>
                        <!-- FOR PROFILE PICTURE -->
                        <div class="profilePictureHolder" style="border: none;">
                            <div class="topInfo clearfix" style='border-bottom: 1px solid #eee;padding-bottom: 15px;'>
                                <div class="col-lg-4 in pull-left">
                                    <h5><a href="mailto::Info@trackasins.com">Info@trackasins.com</a></h5>
                                    <h5>Location: Rockland County, NY</h5>
                                    <h5>Phone Number: 1-845-630-8226</h5>
                                </div>
                            </div>
                            <div class='form'><br />
                            <?php echo form_open('settings/change_password/changePasswordProcess', array('id' => 'settings_change_password_form', 'action' => '')); ?>
                                <div class="inputType">
                                    <label>Your name</label>
                                    <input type="text" class="" id="your_name" name="your_name" placeholder="Your Full Name" />
                                </div>
                                <div class="inputType">
                                    <label>Your Email</label>
                                    <input type="email" class="" id="your_email" name="your_email" placeholder="Your Email" />
                                </div>
                                <div class="inputType">
                                    <label>Subject</label>
                                    <input type="text" class="" id="subject" name="subject" placeholder="Subject" />
                                </div>
                                <div class="inputType">
                                    <label>Message Body</label>
                                    <textarea class="" id="message_body" name="message_body" placeholder="How can we help?"></textarea>
                                </div>
                                <div class="bottomProfilePicForm">
                                    <input type="submit" class="btn btn-embossed btn-success primarycolorbtn" value="Send Message" />
                                </div>
                                <?php echo '</form>'; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>