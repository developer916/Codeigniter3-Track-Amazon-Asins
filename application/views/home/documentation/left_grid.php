<div class="settingsNav col-lg-3 col-sm-12 pull-left">
    <div class="innerNav card card-default">
        <div class="topCard">
            <h3>Recent articles</h3>
        </div>
        <?php $current_url = current_url();?>
        <div class="innerNav">
            <ul class="nav" id="myUL">
                <li <?php if($current_url == base_url('/documentation/articles')) { echo "class='active'"; }?>><a href="<?php echo base_url(); ?>documentation">Articles</a></li>
                <li <?php if($current_url == base_url('/documentation/sentences')) { echo "class='active'"; }?>><a href="<?php echo base_url(); ?>documentation/sentences">Sentences</a></li>
            </ul>
        </div>
    </div>
</div>