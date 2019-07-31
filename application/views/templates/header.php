<?php
    switch($site_page)
    {
        case 'dashboard':
        case 'notification':
        case 'report':
        case 'settings':
        case 'help':
        case 'blog':
        case 'documentation':
            /*$this->user->IsLoggedIn();*/
            /*break;*/
    }

    if(isset($_SESSION['uid'])){
        $user = $this->db->query("SELECT * FROM users WHERE ID='".$_SESSION['uid']."'")->row();
        if(isset($user)){
            if(isset($user->created_at)){
                $today = date_create(date('Y-m-d'));
                $created = date_create(substr($user->created_at,0,10));
                $diff = date_diff($created,$today);
                $difference_date = $diff->days;
            }
        }
    }

?>


<!DOCTYPE html>
<html>
<head>
    <style>
        button, input[type="submit"]{
            outline: none !important;
        }
    </style>
    <!-- meta -->

    <!-- <meta charset="<?php echo strtolower($site_info['site_utf']); ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    SEO Meta
    <meta name="description" content="<?php echo $site_info['site_description']; ?>">
    <meta http-equiv="content-type" content="text/html;charset=<?php echo $site_info['site_utf']; ?>"> -->

    <!-- header -->



    <title>Track ASINS <?php echo isset($title_addition) ? ' | ' . $title_addition : ''; ?> </title>

<!--    <link rel="stylesheet" href="--><?php //echo base_url() ?><!--assets2/datepicker/css/bootstrap-datepicker.min.css">-->
    <!-- Stylesheets -->
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Open+Sans" >
    <link href="<?php echo base_url('assets2/css/'.$stylesheet.'.css'); ?>" rel="stylesheet">
    <!--not accepted the css and js then custum link-->
    <link href="<?php echo base_url('assets2/css/settings.css'); ?>" rel="stylesheet">
    <link rel="stylesheet" href="<?php base_url()?>/assets2/global/plugins/bootstrap-sweetalert/sweetalert.css">
    <link rel="stylesheet" type="text/css" href="<?php base_url()?>/assets2/css/jquery.fancybox.min.css">
    <style>

    </style>
    <!--end here custum creating links-->
    
    <!-- <link rel="stylesheet" href="http://s.mlcdn.co/animate.css"> -->
    <!-- <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css" rel="stylesheet"> -->
    <script src="https://use.fontawesome.com/b38b622847.js"></script>
</head>
<body>
<div class="loading" id="loadingSpinner" style="display: none">Loading&#8230;</div>
<nav class="navbar navbar-fixed-top topHeaderMainLogged ">
    <div class="container-fluid mainTopHeader ">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="gn-icon gn-icon-menu" href="javascript:void(0)" style="display: inline-block!important;">
                <span>Menu</span>
            </a>
            <a class='navbar-brand' href='<?php echo base_url(); ?>dashboard' style="margin-bottom: 12px;">
                <img src='/assets2/images/TrackASIN.png'style="max-height:62px;">
            </a>
        </div>
        <div class="collapse mainLoggedNav navbar-right navbar-collapse" id="bs-example-navbar-collapse-1">
            <ul class="nav navbar-nav right-nav-bar">
                <?php if(isset($_SESSION['user_id'])){ ?>
                    <li><a href="<?php echo base_url(); ?>dashboard">Dashboard</a></li>
                    <li><a href="<?php echo base_url(); ?>Notification">Notifications</a></li>
                    <li><a href="<?php echo base_url(); ?>Report">Reports</a></li>
                    <li class="dropdown" id="userProfileDropdown">
                        <a href="<?php echo base_url(); ?>settings" class="dropdown-toggle hidden-xs " data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false" id="dropdown-user">
                            <div class="userBox">
                                <?php
                                $user_id = ($this->session->userdata('user_id'));
                                $result = $this->common_model->getDataSingleRow('users',array('id'=>$user_id));
                                ?>
                                <div class="usrPP pull-left" style="position: relative; background-image: url(<?php echo base_url(); ?>assets2/user_data/<?php echo $result->profile_pic; ?>);" ></div>
                                <div class="userInfo">
                                    <h3>
                                        <?php
                                            echo ($result->fname);
                                        ?>
                                        <span class="caret"></span>
                                    </h3>

                                </div>
                            </div>
                        </a>
                        <a href="<?php echo base_url(); ?>settings" class="dropdown-toggle hidden-lg hidden-md hidden-sm" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false" id="dropdown-user">
                            <?php
                                echo ($result->fname);
                            ?>
                            <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a href="<?php echo base_url(); ?>settings"><i class="fa fa-fw fa-cog" aria-hidden="true"></i> Settings & Account</a></li>
                            <li><a href="<?php echo base_url(); ?>blog"><i class="fa fa-fw fa-list" aria-hidden="true"></i> Blog </a></li>
                            <li><a href="<?php echo base_url(); ?>help"><i class="fa fa-fw fa-question-circle" aria-hidden="true"></i> Help & Support</a></li>
                            <li><a href="<?php echo base_url(); ?>documentation"><i class="fa fa-fw fa-file " aria-hidden="true"></i> Documentation</a></li>
                            <li role="separator" class="divider"></li>
                            <li><a href="<?php echo base_url(); ?>logout_ctrl"><i class="fa fa-fw fa-sign-out" aria-hidden="true"></i>Logout</a></li>
                        </ul>
                    </li>
                <?php }?>
            </ul>
        </div>
    </div>
</nav>
<nav class="gn-menu-wrapper">
    <div class="gn-scroller">
        <ul class="gn-menu">
            <li><a class="gn-icon">Coming Soon</a></li>
        </ul>
    </div><!-- /gn-scroller -->
</nav>
<?php /*$this->header->render($site_page);*/ ?>
