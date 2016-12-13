<html>
<head>
    <title>Upload Form</title>
</head>
<body>

    <?php echo $error;?>

    <?php echo form_open_multipart('upload/do_upload');?>
        <input <?php echo set_checkbox('remove_whitespace', 1, TRUE) ?> name="remove_whitespace" type="checkbox" value="1" />Remove Whitespace<br />

        <input <?php echo set_checkbox('border', 1, TRUE) ?> name="border" type="checkbox" value="1" />Border
        <input <?php echo set_value('color', '#4267b2') ?> name="color" type="color" value="#4267b2" />Color<br />

        <input <?php echo set_checkbox('watermark', 1, TRUE) ?> name="watermark" type="checkbox" value="1" />Watermark
        <input <?php echo set_value('watermark_percentage') ?> name="watermark_percentage" type="number" value="50" />%<br />

        <input multiple required type="file" name="userfile[]" size="20" />

        <br /><br />

        <input name="fileSubmit" type="submit" value="Upload" />
    </form>

</body>
</html>
