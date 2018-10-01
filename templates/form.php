<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css">
<h1>Websites loading time benchmark comparation</h1>
<form name="form" method="post" action="index.php">
    <table id="table" class="table table-bordered table-striped">
        <tr>
            <td valign="top">
                <label for="email">Email for reports *</label>
            </td>
            <td valign="top">
                <input  type="email" name="email" maxlength="50" size="30" value="flamastertest@gmail.com" required>
            </td>
        </tr>
        <tr>
            <td valign="top">
                <label for="domain">Website *</label>
            </td>
            <td valign="top">
                <input  type="text" name="domain" maxlength="50" size="30" value="<?= $domain ?>" required>
            </td>
            <?php foreach ($render as $k => $val) { ?>
                <td valign="top">
                    <label for="domain"><?= $k ?> time</label>
                </td>
                <td valign="top">
                    <input  type="text" name="total_time" maxlength="50" size="30" value="<?= $val ?>" disabled>
                </td>
                <td valign="top">
                    <label></label>
                </td>
                <td valign="top">
                    <button type="submit" class="show_details" name="show_details" value="<?= $info['url'] ?>">Show details</button>
                </td>
            <?php } ?>
        </tr>
        <?php if($restClient->error) { ?>
            <tr>
                <td colspan="2" style="text-align:center">
                    <span style="color:red;"><?= $restClient->error ?></span>
                </td>
            </tr>
        <?php } ?>
        <?php foreach ($responses as $s => $site) { ?>
            <tr>
                <td valign="top">
                    <label for="domain">Website to compare</label>
                </td>
                <td valign="top">
                    <input  type="text" name="compare[]" maxlength="50" size="30" value="<?= $site['url'] ?>" disabled>
                </td>
                <td valign="top">
                    <label for="domain">Total time</label>
                </td>
                <td valign="top">
                    <input  type="text" name="total_time" maxlength="50" size="30" value="<?= $site['total_time'] ?>" disabled>
                </td>
                <td valign="top">
                    <label>difference: <?= round(($val-$site['total_time']), 6) ?> </label>
                </td>
                <td valign="top">
                    <button type="submit" class="show_details" name="show_details" value="<?= $site['url'] ?>">Show details</button>
                </td>
            </tr>
        <?php } ?>
    </table>
    <input type="submit" value="Check loading time">
</form>
<button onclick="add_tel();">Add website to compare</button>

<div id="postinfo">
    <?php
    if(isset($_POST['show_details'])) {
        $url = filter_var($_POST['show_details'], FILTER_SANITIZE_URL);
        if ($url)
            include 'src/pagespeed.php';
    }
    ?>
</div>
<!--blocks the pop up asking for form resubmission on refresh-->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script>
    if ( window.history.replaceState ) {
        window.history.replaceState( null, null, window.location.href );
    }
    function add_tel(){
        $('#table').append('<tr><td valign="top"><label for="domain">Website</label></td><td valign="top"><input  type="text" name="compare[]" maxlength="50" size="30" value="" required></td></tr>');
    }
    $( ".show_details" ).click(function( event ) {
//        event.preventDefault();
        $.ajax({
            type: "POST",
            url: location.href,
            processdata: false,
            data: $('#form').serialize() + "&show_details=" + $(this).val(),
            success: function(data) {
                $("#postinfo").load(" #postinfo");
            }
        });
    });
</script>