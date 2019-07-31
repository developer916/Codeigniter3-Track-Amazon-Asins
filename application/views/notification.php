<style>
    td:hover a{
        color: #d27842 !important;
    }
</style>
<div class="reportsHeadline headline headline-site-color">
    <div class="container-fluid inner">
        <div class="topHeadline container text-center">
            <h3>Notifications</h3>
        </div>
    </div>
</div>
<div class="mainNotificationsContainer container">
    <div class="mainIndividualCont innerContainer col-lg-12" data-open="mainIndividualCont">
        <div class="topHeadPart">
            <h3>In and Out of Stock Notifications</h3>
        </div>
        <div class="bottomHolder">
<!--            <table class="mainTable table table-striped table-bordered table-hover individual-item-report dataTable no-footer" id="DataTables_Table_0" role="grid" aria-describedby="DataTables_Table_0_info">-->
            <table class="mainTable table table-striped table-bordered table-hover individual-item-report dataTable main-table" id="DataTables_Table_1" role="grid" aria-describedby="DataTables_Table_1_info" style="width:100%" >
                <thead>
                <br />
                <tr role="row">
                    <th class="text-center verticle-middle sorting_disabled" data-orderable="false" rowspan="1" colspan="1" aria-label="Image" style="width: 53px;">
                        <div>Image</div>
                    </th>
                    <th class="text-center verticle-middle sorting_disabled" tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="Title: activate to sort column ascending" style="width: 424px;">
                        Item Title
                    </th>
                    <th class="text-center verticle-middle sorting_disabled" tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="ASIN: activate to sort column ascending" style="width: 61px;">
                        ASIN
                    </th>
                    <th class="text-center verticle-middle sorting" tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="Report: activate to sort column ascending" style="width: 218px;">
                        Is Amazon still out of stock
                    </th>
                    <th class="text-center verticle-middle sorting" tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="Report: activate to sort column ascending" style="width: 218px;">
                        Are you in stock?
                    </th>
<!--                    <th class="text-center verticle-middle menuListOpen dropbox" data-orderable="false" tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="Report: activate to sort column ascending" style="width: 48px;">-->
<!--                        <div class="dropdown-toggle" data-toggle="dropdown">Bulk Action<br/><span style="color: #ddd"><i class="fa fa-caret-down" aria-hidden="true"></i></span></div>-->
<!--                        <ul class="dropdown-menu">-->
<!--                            <li><a href="#">Select All</a></li>-->
<!--                            <li><a href="#">Turn tracking on</a></li>-->
<!--                            <li><a href="#">Turn tracking off</a></li>-->
<!--                            <li><a href="#">Delete</a></li>-->
<!--                        </ul>-->
<!--                    </th>-->
                </tr>
                </thead>
                <tbody id="notificationTbody">
                <?php
                // So this is how we can make the text smnller. This should work fine
                // $first_title = "Acer H277HU kmipuz 27-Inch IPS WQHD 2560 x 1440 Display, USB 3.1 Type-C port, HDMI, DP, 2 x 3w speakers";
                // $second_title = "Acer K272HUL Cbmidp Black 27\" WQHD HDMI DisplayPort Widescreen LED Backlight LCD Monitor Built-in Speakers";

                // if(strlen($first_title) > 75)
                // {
                //     $first_titlec = substr($first_title, 0, 75) . "...";
                // }

                // if(strlen($second_title) > 75)
                // {
                //     $second_titlec = substr($second_title, 0, 75) . "...";
                // }

                $user_id=$this->session->userdata('user_id');
                $query = $this->db->query("SELECT * FROM `notification` where user_id = '$user_id' ORDER BY date LIMIT 100 ")->result();
                         //print_r($query);
                         foreach ($query as $query) {
                                                    
                ?>
                <tr role="row" class="">


                    <td class="text-center vartical-middle">
                        <a href="<?php echo $query->image; ?>" data-fancybox="images" data-caption="<?php echo $query->title_name; ?>">
                            <?php echo "<img src='".$query->image. "' style='width:60px;'/>" ?>
                        </a>
                     <!-- <a target="_blank" href="http://www.amazon.com/Acer-H277HU-kmipuz-27-Inch-speakers/dp/B01B64O3M4%3FSubscriptionId%3DAKIAIJEQX7SVEBFFDYEA%26tag%3Dwafi03-20%26linkCode%3Dxm2%26camp%3D2025%26creative%3D165953%26creativeASIN%3DB01B64O3M4"><img src="http://ecx.images-amazon.com/images/I/51A1VOtlVML._SL75_.jpg" alt=""></a>
                      -->
                    </td>
                    <td class="text-center verticle-middle" title='<?php echo $query->title_name; ?>'>
                        <a target="_blank"
                           href="http://amazon.com/dp/<?php echo $query->asin; ?>"><?php echo $query->title_name; ?></a>
                    
                    </td>
                    <td class="text-center verticle-middle">
                      <a target="_blank"
                         href="http://amazon.com/dp/<?php echo $query->asin; ?>"><?php echo $query->asin;?></a>
                   
                    </td>
                    <?php if(($query->amznotseller=="1")  ) { ?>
                                <td class="text-center b red verticle-middle">
                               <span style="color:green; font-size:25px;margin-left: -20px;">Yes</span>
                              </td>
                            <?php }
                            ?>
                            <?php if(($query->amznotseller=="0")  ) { ?>
                                <td class="text-center b red verticle-middle">
                               <span style="color:black; font-size:25px;margin-left: -20px;">No</span>
                                </td>
                            <?php }
                            ?>
                   <?php if(($query->sellerstock=="1")  ) { ?>
                                <td class="text-center b red verticle-middle">
                               <span style="color:green; font-size:25px;margin-left: -20px;">Yes</span>
                              </td>
                            <?php }
                            ?>
                            <?php if(($query->sellerstock=="0")  ) { ?>
                                <td class="text-center b red verticle-middle">
                               <span style="color:black; font-size:25px;margin-left: -20px;">No</span>
                                </td>
                            <?php }
                            ?>
<!--                    <th class="text-center c-hold verticle-middle">-->
<!--                        <input type='checkbox' value='' id='checkbox1' />-->
<!--                        <label for='checkbox1' class='cb-label'></label>-->
<!--                    </th>-->
                    
                </tr>
                <tr  role="row" class="">

					<td style='width: 100%;padding: 20px;' colspan='5'>
						<div>
							<ul class="activityHolder">
                                <?php if($query->amznotseller=="1"): ?>
                                <li class="clearfix out-of-stock"  >
                                    <div class="col2 col-lg-1">
                                        <div class="date" > Just now </div>
                                    </div>

									<div class="col1 col-lg-11">
										<div class="contm">
											<div class="cont-col1">
												<div class="label label-sm label-info" style="font-size: 1.5em;background: #d27842">
                                                    <i class="fa fa-smile-o" aria-hidden="true"></i>

                                                </div>
											</div>
											<div class="cont-col2">

												<div class="desc">Amazon ran out of stock on
                                                <?php echo date("F d, Y", strtotime($query->date)); ?>
                                                </div>
											</div>
										</div>
									</div>
								</li>
                                <?php endif; ?>
                                <?php if($query->amznotseller=="0"): ?>
								 <li class="clearfix in-stock-on">
                                     <div class="col2 col-lg-1">
                                         <div class="date" style="color: white;"> Just now </div>
                                     </div>
									<div class="col1 col-lg-11">
										<div class="contm">
											<div class="cont-col1">
												<div class="label label-sm label-info" style="font-size: 1.5em;background: #aaa">
                                                    <i class="fa fa-frown-o" aria-hidden="true"></i>

                                                </div>
											</div>
											<div class="cont-col2">
												<div class="desc">Amazon back in stock on <?php echo date("F d, Y", strtotime($query->date));?></div>
											</div>
										</div>
									</div>

								</li>
                                 <?php endif; ?>
							</ul>
						</div>
					</td>
                    <td style="display: none;"></td>
                    <td style="display: none;"></td>
                    <td style="display: none;"></td>
                    <td style="display: none;"></td>

				</tr>
                <?php } ?>
                </tbody>
            </table>
            <!--<tr role="row" class="even">

                    <td class="text-center vartical-middle">
                         <a target="_blank" href="http://www.amazon.com/Acer-K272HUL-DisplayPort-Widescreen-Backlight/dp/B018J0216S%3FSubscriptionId%3DAKIAIJEQX7SVEBFFDYEA%26tag%3Dwafi03-20%26linkCode%3Dxm2%26camp%3D2025%26creative%3D165953%26creativeASIN%3DB018J0216S"><img src="http://ecx.images-amazon.com/images/I/51HNYCJmZ-L._SL75_.jpg" alt=""></a>

                    Not Found
                     </td>
                    <td class="text-center verticle-middle" title='<?php /*echo $second_title;*/ ?>'>
                        <?php /*echo $second_titlec;*/ ?>
                    Not Found
                    </td>
                    <td class="text-center verticle-middle">
                       <!--  <a target="_blank" href="http://www.amazon.com/Acer-K272HUL-DisplayPort-Widescreen-Backlight/dp/B018J0216S%3FSubscriptionId%3DAKIAIJEQX7SVEBFFDYEA%26tag%3Dwafi03-20%26linkCode%3Dxm2%26camp%3D2025%26creative%3D165953%26creativeASIN%3DB018J0216S">B018J0216S</a>

                    Not Found
                    </td>
                    <th class="text-center b green verticle-middle">
                    Not Found
                    </th>
                    <th class="text-center b green verticle-middle">
                    Not Found
                    </th>
                    <!--<th class="text-center c-hold verticle-middle">
                        <input type='checkbox' value='' id='checkbox1' />
                        <label for='checkbox1' class='cb-label'></label>
                    </th>
                </tr>-->

            <!-- <center>
                <p>Notification Not Found</p>
            </center> -->
            <!-- <div class="loadMoreHolder text-center" style="padding-top: 15px;">
                <button class="btn btn-wide btn-embossed btn-primary" style='background: #b65f2b'><i class="fa fa-arrow-down" aria-hidden="true"></i></button>
            </div> -->
        </div>
    </div>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>

<script src="//cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>


<script>
    $(document).ready(function(){

    });
</script>
