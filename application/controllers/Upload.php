<?php
ini_set('memory_limit', '-1');
class Upload extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('form', 'url'));
		$this->load->library('image_moo');
	}

	public function index()
	{
		$data = array('error' => '');
		$this->load->view('upload/upload_form', $data);
	}

	public function do_upload()
	{
		$data = array('message' => '');

		$remove_whitespace = $this->input->post('remove_whitespace');
		$border = $this->input->post('border');
		$color = $this->input->post('color');
		$watermark = $this->input->post('watermark');
		$watermark_percentage = $this->input->post('watermark_percentage');
		$watermark_text = $this->input->post('watermark_text');

		if (isset($_FILES['userfile']['name']))
		{
			$userfile_count = count($_FILES['userfile']['name']);
			for ($i = 0; $i < $userfile_count; $i++)
			{
				$_FILES['file']['error'] = $_FILES['userfile']['error'][$i];
                $_FILES['file']['name'] = $_FILES['userfile']['name'][$i];
				$_FILES['file']['tmp_name'] = $_FILES['userfile']['tmp_name'][$i];
				$_FILES['file']['size'] = $_FILES['userfile']['size'][$i];
				$_FILES['file']['type'] = $_FILES['userfile']['type'][$i];

				$config['allowed_types']        = 'gif|jpg|png';
				$config['max_size']             = 5000;
				$config['max_width']            = 5120;
				$config['max_height']           = 5120;
				$config['mod_mime_fix'] = FALSE;
				$config['overwrite'] = TRUE;
				$config['remove_spaces'] = FALSE;
				$config['upload_path']          = './uploads/';
				$this->load->library('upload', $config);

				if ( ! $this->upload->do_upload('file'))
				{
					$data['message'][] = $_FILES['file']['name'].', '.$this->upload->display_errors();
				}
				else
				{
					$upload_data = $this->upload->data();

					if ($remove_whitespace == 1)
						$upload_data = $this->do_remove_whitespace($upload_data);

					$upload_data = $this->do_square($upload_data); // square without border

					if ($watermark == 1)
						$upload_data = $this->do_watermark($upload_data, $watermark_percentage);

					if ($watermark_text == 1)
						$upload_data = $this->do_watermark_text($upload_data);

					$upload_data = $this->do_square(
						$upload_data,
						($border == 1 ? TRUE : FALSE),
						$color
					);

					$data['message'][] = $_FILES['file']['name'].', success';
				}
			}
		}

		$this->load->view('upload/upload_success', $data);
	}

	public function do_remove_whitespace($data)
	{
		// pr($data);

		$imgSrcPathFinal = $data['full_path']; // absolute path of source image or relative path to source directory with image name.
		$imgDestPath = $data['file_path'];  // relative path to destination directory.
		$imgName = $data['file_name']; // image name
		$types 			= array(1 => 'gif', 2 => 'jpg', 3 => 'png', 4 => 'swf', 5 => 'psd', 6 => 'bmp', 7 => 'tiff', 8 => 'tiff', 9 => 'jpc', 10 => 'jp2', 11 => 'jpx', 12 => 'jb2', 13 => 'swc', 14 => 'iff', 15 => 'wbmp', 16 => 'xbm');
		list($width, $height, $type, $attr) = getimagesize($imgSrcPathFinal);
		$fileExtension = $types[$type];
		if ($fileExtension == "jpg")
		{
			$img 		= imagecreatefromjpeg($imgSrcPathFinal) or die("Error Opening JPG");
			$quality 	= 95;
			$valid_ext 	= 1;
		}
		else if ($fileExtension == "gif")
		{
			$imgorg		= imagecreatefromgif($imgSrcPathFinal) or die("Error Opening GIF");

			$img 		= imagecreatetruecolor($width, $height);
			$white 		= imagecolorallocate($img, 255, 255, 255);
			imagefill($img, 0, 0, $white);

			imagecopyresampled(
				$img, $imgorg,
				0, 0, 0, 0,
				$width, $height,
				$width, $height);

			$quality 	= 95;
			$valid_ext 	= 1;
		}
		else if ($fileExtension == "png")
		{
			$imgorg		= imagecreatefrompng($imgSrcPathFinal) or die("Error Opening PNG");

			$img 		= imagecreatetruecolor($width, $height);
			$white 		= imagecolorallocate($img, 255, 255, 255);
			imagefill($img, 0, 0, $white);

			imagecopyresampled(
				$img, $imgorg,
				0, 0, 0, 0,
				$width, $height,
				$width, $height);

			$quality 	= 9;
			$valid_ext 	= 1;
		}

		if($valid_ext){
			$img_top 		= 0;
			$img_bottom     = 0;
			$img_left 		= 0;
			$img_right 		= 0;

			//top
			for($img_top = 0; $img_top < imagesy($img); ++$img_top) {
			  for($x = 0; $x < imagesx($img); ++$x) {
				if(imagecolorat($img, $x, $img_top) != 0xFFFFFF) {
				   break 2; //out of the 'top' loop
				}
			  }
			}

			//bottom
			for($img_bottom = 0; $img_bottom < imagesy($img); ++$img_bottom) {
			  for($x = 0; $x < imagesx($img); ++$x) {
				if(imagecolorat($img, $x, imagesy($img) - $img_bottom-1) != 0xFFFFFF) {
				   break 2;// out of the 'bottom' loop
				}
			  }
			}

			//left
			for($img_left = 0; $img_left < imagesx($img); ++$img_left) {
			  for($y = 0; $y < imagesy($img); ++$y) {
				if(imagecolorat($img, $img_left, $y) != 0xFFFFFF) {
				   break 2; //out of the 'left' loop
				}
			  }
			}

			//right
			for($img_right = 0; $img_right < imagesx($img); ++$img_right) {
			  for($y = 0; $y < imagesy($img); ++$y) {
				if(imagecolorat($img, imagesx($img) - $img_right-1, $y) != 0xFFFFFF) {
				   break 2; //out of the 'right' loop
				}
			  }
			}

			$newimg_width = $width;
			if(($img_left + $img_right) < $width){
				$newimg_width = $width-($img_left+$img_right);
			}
			$newimg_height = $height;
			if(($img_top+$img_bottom) < $height){
				$newimg_height = $height-($img_top+$img_bottom);
			}
			$newimg = imagecreatetruecolor($newimg_width, $newimg_height);
			imagecopy($newimg, $img, 0, 0, $img_left, $img_top, $newimg_width, $newimg_height);
			imagedestroy($img);
			unset($img);
			if($fileExtension == "gif"){
				imagegif($newimg, $imgDestPath.$imgName, $quality)  or die("Cant save GIF");
			}
			else if($fileExtension == "jpg"){;
				imagejpeg($newimg, $imgDestPath.$imgName, $quality)  or die("Cant save JPG");
			}
			else if($fileExtension == "png"){
				imagepng($newimg, $imgDestPath.$imgName, $quality)  or die("Cant save PNG");
			}
		}

		list($width, $height) = getimagesize($data['full_path']);
		$data['image_height'] = $height;
		$data['image_width'] = $width;

		return $data;
	}

	public function do_square($data, $border = FALSE, $border_color = '#4267b2')
	{
		// pr($data);

		$full_path = $data['full_path'];

		// get max height / width to make 1 : 1
		$max_height_width = max($data['image_height'], $data['image_width']);

		$border_width = 0;
		if ($border == TRUE)
			$border_width = ($max_height_width / 15);

		$this->image_moo->load($full_path)
			// ->set_background_colour("#000000")
			->resize(
				($max_height_width + ($border_width * 2)),
				($max_height_width + ($border_width * 2)),
				TRUE
			);

		if ($border === TRUE)
			$this->image_moo->border($border_width, $border_color);
			// ->border(($max_height_width / 20), "#57b150")
			// ->border(($max_height_width / 20), "#c30f42")
			// ->border(($max_height_width / 20), "#ff6635")

		$this->image_moo->save($full_path, TRUE);

		return $data;
	}

	public function do_watermark($data, $watermark_percentage = 50)
	{
		// pr($data);

		$full_path = $data['full_path'];

		// get min height / width to make 1 : 1
		$min_height_width = min($data['image_height'], $data['image_width']);

		$logo_path = './uploads/logo/logo.png';
		$logo_path_resize = './uploads/logo/logo_resize.png';
		$this->image_moo->load($logo_path)
			->resize($min_height_width, $min_height_width, TRUE)
			->save($logo_path_resize, TRUE);

		$this->image_moo->load($full_path)
			->load_watermark($logo_path_resize, 1, 1)
			->set_watermark_transparency($watermark_percentage)
			->watermark(5)
			->save($full_path, TRUE);

		return $data;
	}

	public function do_watermark_text($data)
	{
		// pr($data);

		$full_path = $data['full_path'];

		// get min, max height / width to make 1 : 1
		$min_height_width = min($data['image_height'], $data['image_width']);
		$max_height_width = max($data['image_height'], $data['image_width']);

		$logo_path = './uploads/logo/watermark_text.jpg';
		$logo_path_resize = './uploads/logo/watermark_text_resize.png';
		$this->image_moo->load($logo_path)
			->resize($max_height_width * 2 / 3, NULL)
			->save($logo_path_resize, TRUE);

		$config['wm_hor_alignment'] = 'left';
		$config['source_image'] = $full_path;
		$config['wm_opacity'] = '100';
        $config['wm_overlay_path'] = './uploads/logo/watermark_text_resize.png';
		$config['wm_type'] = 'overlay';
		$config['wm_vrt_alignment'] = 'top';
		$config['wm_x_transp'] = '1';
		$config['wm_y_transp'] = '1';
		$this->image_lib->initialize($config);
        $this->image_lib->watermark();

		return $data;
	}
}
?>