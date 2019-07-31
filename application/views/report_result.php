<div class="reportsHeadline headline headline-site-color">
    <div class="container-fluid inner">
        <div class="topHeadline container text-center">
            <h3>Report Result</h3>
        </div>
    </div>
</div>
<div class="mainReportsContainer container">
    <div class="mainIndividualCont innerContainer col-lg-12" data-open="mainIndividualCont">
        <div class="topHeadPart">
            <h3>Report Result</h3>
        </div>
        <div class="bottomHolder">
            <div class="listHolder">
                <table class="mainTable gg table table-striped table-bordered table-hover individual-item-report dataTable no-footer" id="DataTables_Table_0" role="grid" aria-describedby="DataTables_Table_0_info">
                    <thead>
                    <tr role="row">
                        <th class="text-center sorting" tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="Title: activate to sort column ascending" style="width: 424px;">
                            Title
                        </th>
                        <th class="text-center sorting" tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="ASIN: activate to sort column ascending" style="width: 100px">
                            ASIN
                        </th>
                        <th class="text-center sorting" tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="Out of Stock Date: activate to sort column ascending" style="width: 61px;">
                            Out of Stock Date
                        </th>
                        <th class="text-center sorting" tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="Back in Stock Date: activate to sort column ascending" style="width: 100px;">
                            Back in Stock Date
                        </th>
                        <th class="text-center sorting" tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="Number days Amazon Out of Stock: activate to sort column ascending" style="width: 61px;">
                            Number days Amazon Out of Stock
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach($reports as $product): ?>
                    <tr role="row" class="odd">
                        <td class="text-center vertical-middle">
                            <?php echo $product->title_name; ?>
                         </td>
                        <td class="text-center vertical-middle">
                            <a target="_blank" href="http://amazon.com/dp/<?php echo $product->asin; ?>">
                                <?php echo $product->asin; ?>
                            </a>
                         </td>
                        <td class="text-center vertical-middle">
                            <?php echo $product->amznotseller == '1' ? date('Y-m-d', strtotime($product->date)) : ''; ?>
                        </td>
                        <td class="text-center vertical-middle">
                            <?php echo $product->amznotseller == '0' ? date('Y-m-d', strtotime($product->date)) : ''; ?>
                        </td>
                        <td class="text-center vertical-middle">
                            <?php echo $product->days; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    var currentDate = '<?php echo date('Y-m-d') ?>';
</script>