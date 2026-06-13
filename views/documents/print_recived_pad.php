<html>
<?php
include('qrcode/qrlib.php');


?>
	<head>
        <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous"/>
        <script src="<?php echo base_url();?>assets/global/plugins/jquery.min.js" type="text/javascript"></script>
        <title>Recived Pad</title>
		<style>
			*{
				margin:0;
				padding:0;
			}
			.left{
				float:left;
				display:inline;
			}
			.right{
				float:right;
				display:inline;
			}
			.clear{
				clear:both;
			}
			.header{
				width:100%;
				height:auto;
			}
			.logo{
				width:20%;
			}
			.college_name{
				width:76%;
				padding:2%;
			}
			.text-center{
				text-align:center;
			}
			.line-thick{
				border-bottom:4px solid #00ac54;
				margin-bottom:1px;
			}
			.line-normal{
				border-bottom:2px solid #00ac54;
				margin-bottom:2px;
			}
			.line-thin{
				border-bottom:1px solid #00ac54;
				margin-bottom:1px;
			}
			.body{
				width:100%;
				height:800px;
			}
            .body::before {
                background-image: url(<?php echo base_url();?>/uploads/<?php echo $compuses->logo; ?>);
                background-size: 50%;
                background-position: 51% 75%;
                background-repeat: no-repeat;
                content: "";
                display: block;
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: 1;
                opacity: 0.2;
            }

			.ref_no{
				width:50%;
				margin-top:20px;
			}
			.dated{
				width:50%;
				margin-top:20px;
				text-align:right;
			}
			.underline{
				text-decoration:underline;
			}
			.footer{
				width:100%;
			}
			
		</style>
	</head>
	<body>
<?php
$book_number = $bookno;
?>

<!--    <div class="" id="hide_div" style="margin:0 auto;height:42px;width:765px;padding:20px;">-->
<!--        <h2>Select Campus</h2>-->
<!--        <select name="cmp_get" id="cmp_get" onchange="myFunction()">-->
<!--            <option>Select Campus</option>-->
<!--            --><?php
//            $a=0;
//            foreach($compuses as $list):
//                ?>
<!--                <option value="--><?php //echo $list['campus_name'];?><!--">--><?php //echo $list['campus_name'];?><!--</option>-->
<!--                --><?php
//                $a++;
//            endforeach;
//            ?>
<!--        </select>-->
<!--    </div>-->

    <div class="" id="show_div" style="margin:0 auto;height:52px;width:765px;padding:20px;">
        <input type="hidden" id="book_number_get" value="<?php echo $book_number ?>">
        <input type="hidden" id="branch_code_get" value="<?php echo $compuses->campus_code ?>">
        <input type="hidden" id="created_by_code_get" value="<?php echo $this->session->userdata('user_id')?>">
        <button class="ajax_call_for_book_no_store" style="background-color: #46aae9;color: white;padding: 10px 10px;border:2px solid white;border-radius: 5px">Ready For Print</button>
    </div>
        <?php

        for($i = 001; $i < 101; $i++){

            QRcode::png(current_url().'/'.$book_number.'/'.$i, 'qr/'.$book_number.'-'.$i.'.png');

            ?>
            <div class="container" style="margin:0 auto;
				height:992px;
				width:765px;
				padding:20px;
				background-position:-55px bottom;
				background-size:30%;
				position:relative;">

                <div class="body">
                    <div style="margin-top:10px;">
                        <h1 style="font-size: 25px;font-style: italic">&nbsp;For Collage Use <span style="font-size:15px;font-weight: normal">(Upload In Software</span> <input type="text" name="input_1" style="width: 50px;height: 30px;"/> <span style="font-size: 17px;font-style: italic">Book No. </span><input type="text" name="input_1"  value="<?php echo $book_number ?>" style="width: 100px;height: 30px;border:none; font-size:20px;border-bottom: 1px solid black"> <span style="font-size: 17px;font-style: italic">Receipt No. </span><input type="text" name="input_1" value="<?php echo str_pad($i, 3, '0', STR_PAD_LEFT);?>" style="width: 100px;font-size:20px;height: 30px;border:none;border-bottom: 1px solid black"></h1>
                    </div>
                    <div style="margin-top:15px;">
                        <div style="border: 1px solid black;width: 33%;float: left">
                            <h3 style="font-size: 18px;font-style: italic;text-align: center;margin-top:10px;">College Use</h3>
                            <h3 style="font-size: 15px;font-style: italic;padding-left: 10px;padding-top: 18px">Student Name:<input type="text" name="input_1" style="width: 140px;height: 13px;border:none;border-bottom: 1px solid black"></h3>
                            <h3 style="font-size: 15px;font-style: italic;padding-left: 10px;padding-top: 18px">Role No:<input type="text" name="input_1" style="width: 176px;height: 13px;border:none;border-bottom: 1px solid black"></h3>
                            <h3 style="font-size: 15px;font-style: italic;padding-left: 10px;padding-top: 18px">Date:<input type="text" name="input_1" style="width: 197px;height: 13px;border:none;border-bottom: 1px solid black"></h3>
                            <h3 style="font-size: 15px;font-style: italic;padding-left: 10px;padding-top: 18px">Amount:<input type="text" name="input_1" style="width: 175px;height: 13px;border:none;border-bottom: 1px solid black"></h3>
                            <h3 style="font-size: 15px;font-style: italic;padding-left: 10px;padding-top: 18px">Recived By:<input type="text" name="input_1" style="width: 155px;height: 13px;border:none;border-bottom: 1px solid black"></h3>
                            <h3 style="font-size: 15px;font-style: italic;padding-left: 10px;padding-top: 18px;padding-bottom: 10px">Sign:<input type="text" name="input_1" style="width: 197px;height: 13px;border:none;border-bottom: 1px solid black"></h3>
                        </div>
                        <div style="border: 1px solid black;width: 33%;float: left">
                            <h3 style="font-size: 18px;font-style: italic;text-align: center;margin-top:10px;">Control Center</h3>
                            <h3 style="font-size: 15px;font-style: italic;padding-left: 10px;padding-top: 18px">Fee Carry By:<input type="text" name="input_1" style="width: 140px;height: 13px;border:none;border-bottom: 1px solid black"></h3>
                            <h3 style="font-size: 15px;font-style: italic;padding-left: 10px;padding-top: 18px">Date:<input type="text" name="input_1" style="width: 197px;height: 13px;border:none;border-bottom: 1px solid black"></h3>
                            <h3 style="font-size: 15px;font-style: italic;padding-left: 10px;padding-top: 18px">Amount:<input type="text" name="input_1" style="width: 175px;height: 13px;border:none;border-bottom: 1px solid black"></h3>
                            <h3 style="font-size: 15px;font-style: italic;padding-top: 9px"><input type="text" name="input_1" style="width:100%;height: 2px;border:none;border-bottom: 1px solid black"></h3>
                            <h3 style="font-size: 18px;font-style: italic;text-align: center;padding-top: 5px">Fee Receive in Control Center</h3>
                            <h3 style="font-size: 15px;font-style: italic;padding-left: 10px;padding-top: 1px">Name:<input type="text" name="input_1" style="width: 190px;height: 13px;border:none;border-bottom: 1px solid black"></h3>
                            <h3 style="font-size: 15px;font-style: italic;padding-left: 10px;padding-top: 1px;">Date:<input type="text" name="input_1" style="width: 197px;height: 13px;border:none;border-bottom: 1px solid black"></h3>
                            <h3 style="font-size: 15px;font-style: italic;padding-left: 10px;padding-top: 1px;">Amount:<input type="text" name="input_1" style="width: 175px;height: 13px;border:none;border-bottom: 1px solid black"></h3>
                            <h3 style="font-size: 15px;font-style: italic;padding-left: 10px;padding-top: 1px;padding-bottom: 6px">Sign:<input type="text" name="input_1" style="width: 197px;height: 13px;border:none;border-bottom: 1px solid black"></h3>
                        </div>
                        <div style="border: 1px solid black;width: 33%;float: left">
                            <h3 style="font-size: 18px;font-style: italic;text-align: center;margin-top:10px;">For Bank</h3>
                            <h3 style="font-size: 15px;font-style: italic;padding-left: 10px;padding-top: 18px">Bank Name:<input type="text" name="input_1" style="width: 146px;height: 13px;border:none;border-bottom: 1px solid black"></h3>
                            <h3 style="font-size: 15px;font-style: italic;padding-left: 10px;padding-top: 18px">Account No:<input type="text" name="input_1" style="width: 146px;height: 13px;border:none;border-bottom: 1px solid black"></h3>
                            <h3 style="font-size: 15px;font-style: italic;padding-left: 10px;padding-top: 18px">Date:<input type="text" name="input_1" style="width: 197px;height: 13px;border:none;border-bottom: 1px solid black"></h3>
                            <h3 style="font-size: 15px;font-style: italic;padding-left: 10px;padding-top: 18px">Amount:<input type="text" name="input_1" style="width: 175px;height: 13px;border:none;border-bottom: 1px solid black"></h3>
                            <h3 style="font-size: 15px;font-style: italic;padding-left: 10px;padding-top: 18px">Submitted By:<input type="text" name="input_1" style="width: 140px;height: 13px;border:none;border-bottom: 1px solid black"></h3>
                            <h3 style="font-size: 15px;font-style: italic;padding-left: 10px;padding-top: 18px;padding-bottom: 10px">Sign:<input type="text" name="input_1" style="width: 197px;height: 13px;border:none;border-bottom: 1px solid black"></h3>
                        </div>
                    </div>
                    <div style="border-bottom: 1px dashed black">&nbsp;</div>
                    <div class="header">
                        <div class="left logo">
                            <img src="<?php echo base_url();?>uploads/<?php echo $compuses->logo; ?>" width="100%" alt="" />
                        </div>
                        <div class="left college_name text-center">
                            <h1 id="camp_name" style="text-transform: uppercase;color: #272579;font-weight: bolder;font-size: 40px;"><?php echo $compuses->campus_name; ?></h1>

                            <br />
                            <!--                        <p>Email : info@shahbazcollegeofpharmacy.edu.pk</p>-->
                        </div>
                    </div>
                    <br>
                    <br>
                    <br>
                    <br>
                    <br>
                    <br>
                    <br>
                    <br>
                    <br>
                    <div style="text-align: center">
                        <p style="display: inline;font-size:15px;text-align: center"><i class="fas fa-mobile-alt"></i>  : <?php echo $compuses->phone; ?> </p> <p style="display: inline;font-size:15px"> <i class="fas fa-envelope-open-text"></i> :  <?php echo $compuses->email; ?> </p> <p style="display: inline;font-size:15px"> <i class="fab fa-internet-explorer"></i> :  <?php echo $compuses->website; ?> </p>
                    </div>
                    <div style="margin-top: 10px">
                        <div style="float: left;width: 69%">
                            <h1 style="font-size: 20px;font-style: italic;margin-top:8px">&nbsp;Name: <input type="text" name="input_1" style="width: 461px;height: 25px;border: 2px solid black;"/> </h1>
                            <h1 style="font-size: 20px;font-style: italic;margin-top:8px">&nbsp;Father Name: <input type="text" name="input_1" style="width: 400px;height: 25px;border: 2px solid black"/> </h1>
                            <h1 style="font-size: 20px;font-style: italic;margin-top:8px">&nbsp;Role No: <input type="text" name="input_1" style="width: 443px;height: 25px;border: 2px solid black"/> </h1>
                            <h1 style="font-size: 20px;font-style: italic;margin-top:8px">&nbsp;Date:  <input type="text" name="input_1" style="width: 50px;height: 25px;border: 2px solid black"/> <input type="text" name="input_1" style="width: 50px;height: 25px;border: 2px solid black"/> &nbsp; &nbsp; <input type="text" name="input_1" style="width: 50px;height: 25px;border: 2px solid black"/> <input type="text" name="input_1" style="width: 50px;height: 25px;border: 2px solid black"/> &nbsp;&nbsp; <input type="text" name="input_1" style="width: 50px;height: 25px;border: 2px solid black"/> <input type="text" name="input_1" style="width: 50px;height: 25px;border: 2px solid black"/> <input type="text" name="input_1" style="width: 50px;height: 25px;border: 2px solid black"/> <input type="text" name="input_1" style="width: 50px;height: 25px;border: 2px solid black"/> </h1>
                            <h1 style="font-size: 20px;font-style: italic;margin-top:8px">&nbsp;Class Name: <input type="text" name="input_1" style="width: 412px;height: 25px;border: 2px solid black"/> </h1>
                            <h1 style="font-size: 20px;font-style: italic;margin-top:8px">&nbsp;Cell Name: <input type="text" name="input_1" style="width: 423px;height: 25px;border: 2px solid black"/> </h1>
                            <h1 style="font-size: 16px;font-style: italic;margin-top:8px">&nbsp;Fee For College: <input type="text" name="input_1" style="width: 50px;height: 25px;border: 2px solid black"/> Supply: <input type="text" name="input_1" style="width: 50px;height: 25px;border: 2px solid black"/> Annual: <input type="text" name="input_1" style="width: 50px;height: 25px;border: 2px solid black"/> Other: <input type="text" name="input_1" style="width: 50px;height: 25px;border: 2px solid black"/> </h1>
                            <h1 style="font-size: 20px;font-style: italic;margin-top:8px">&nbsp;Amount: <input type="text" name="input_1" style="width: 165px;height: 25px;border: 2px solid black"/> Recived By: <input type="text" name="input_1" style="width: 170px;height: 25px;border: 2px solid black"/></h1>
                            <h1 style="font-size: 16px;font-style: italic;margin-top:8px">&nbsp;Fee Submited Through Chaque: <input type="text" name="input_1" style="width: 80px;height: 25px;border: 2px solid black"/> Cash: <input type="text" name="input_1" style="width: 80px;height: 25px;border: 2px solid black"/> </h1>
                            <h1 style="font-size: 16px;font-style: italic;margin-top:8px">&nbsp;Bank Name: <input type="text" name="input_1" style="width: 130px;height: 25px;border-bottom: 1px solid black!important;border:none;"/>&nbsp; Cheque Cash Date: <input type="text" name="input_1" style="width: 130px;height: 25px;border-bottom: 1px solid black!important;border:none;"/> </h1>
                            <h1 style="font-size: 16px;font-style: italic;margin-top:8px">&nbsp;Cheque No: <input type="text" name="input_1" style="width: 430px;height: 25px;border-bottom: 1px solid black!important;border:none;"/>&nbsp;</h1>

                        </div>
                        <div style="float: left;width: 29%">
                            <h1 style="font-size: 20px;font-style: italic;margin-top:8px">&nbsp;Book No: <input type="text" name="input_1" value="<?php echo $book_number ?>" style="font-size: 20px;width: 127px;height: 25px;border:none;border-bottom: 1px solid black"/> </h1>
                            <h1 style="font-size: 20px;font-style: italic;margin-top:8px">&nbsp;Receipt No: <input type="text" name="input_1" value="<?php echo str_pad($i, 3, '0', STR_PAD_LEFT);?>" style="font-size: 20px;width: 110px;height: 25px;border:none;border-bottom: 1px solid black"/> </h1>
                            <h1 style="font-size: 23px;font-style: italic;margin-top:8px">&nbsp; Fee Given By</h1>
                            <h1 style="font-size: 20px;font-style: italic;margin-top:8px">&nbsp;Name: <input type="text" name="input_1" style="width: 150px;height: 25px;border:none;border-bottom: 1px solid black"/> </h1>
                            <h1 style="font-size: 20px;font-style: italic;margin-top:8px">&nbsp;Sign: <input type="text" name="input_1" style="width: 160px;height: 25px;border:none;border-bottom: 1px solid black"/> </h1>
                            <h1 style="font-size: 20px;font-style: italic;margin-top:8px">&nbsp;Cell No: <input type="text" name="input_1" style="width: 135px;height: 25px;border:none;border-bottom: 1px solid black"/> </h1>
                            <div style="border:1px solid black;width:200px;height:130px;margin-top:8px;margin-left: 5px"></div>
                            <h1 style="font-size: 20px;font-style: italic;margin-top:5px">&nbsp;College Stamp </h1>
                        </div>
                    </div>
                </div>
                <br>
                <br>
                <br>
                <br>
                <br>
                <br>
                <div class="student_information" style="border-top:1px solid black">
                    <br>
                    <div style="float:left; display:inline; width:15%;text-align: right">
                        <img src="<?php echo base_url();?>qr/<?php echo $book_number.'-'.$i?>.png" alt="" width="100" />
                        <p style="    text-align: center;font-weight: bolder;font-size: 20px">Orginal</p>
                    </div>
                    <div style="float:left; display:inline; width:85%;text-align: right">
                        <p style="font-size: 12px">  فیس وصولی کے بعد نا قابل واپسی ہے    <i class="fas fa-star"></i></p>
                        <p style="font-size: 12px">  فیس جمع کروانے کے بعد رسید لینا لازمی ہے        <i class="fas fa-star"></i></p>
                        <p style="font-size: 12px">فیس جمع کروانے کے بعد ۸۰۴۲۹۷۷-۰۳۱۵ سے تصدیقی ایس ایم ایس موصول ہو گا اور طالب علم کے آن لا ئن پورٹل پر ظاہر ہو جا ئے گا<i class="fas fa-star"></i></p>
                        <p style="font-size: 12px">چیک کےذریعے جمع کروائی گئ فیس پر چیک کیش نہ ہونے کی صورت میں طالب علم کو500 سے 1000 تک کا جرمانہ یا کالج سے  کیا جا سکتا ہے  <i class="fas fa-star"></i></p>
                        <p style="font-size: 12px">Bank Al Habib For ATM or Bank Both 01210981003570018  بینک میں فیس جمع کروانےکیلئے <i class="fas fa-star"></i></p>
                        <p style="font-size: 12px;text-align: center"> مزید معلومات کیلئے رابطہ نمبر 03158042977</p>
                        <p style="font-size: 12px;text-align: center"> ‌اپنی‌ ‌فیس‌ ‌کا‌ ‌سٹیٹیس‌ ‌چیک‌ ‌کرنے‌ ‌کیلئے‌ ‌کیو‌ ‌آر‌ ‌کوڈ‌ ‌سکین‌ ‌کریں‌</p>
						<p style="font-size: 12px;text-align: center">نوٹ : دو ماہ تک فیس جمع نہ کروانے کی صورت میں آپ کو اگلے سال میں منتقل کر دیا جائے گا اور ایک ماہ مزید فیس جمع نہ کروانے کی صورت میں آپ کا نام کالج سے خارج کر دیا جائے گا‌</p>
         
                    </div>
                    <div style="clear:both;"></div>

                </div>
            </div>
            <div style="clear:both;"></div>
            <br>
            <br>
        <?php
        }

        ?>


<?php

for($i = 001; $i < 101; $i++){

    QRcode::png(current_url().'/'.$book_number.'/'.$i, 'qr/'.$book_number.'-'.$i.'.png');

    ?>
    <div class="container" style="margin:0 auto;
				height:992px;
				width:765px;
				padding:20px;
				background-position:-55px bottom;
				background-size:30%;
				position:relative;">

        <div class="body">
            <div style="margin-top:10px;">
                <h1 style="font-size: 25px;font-style: italic">&nbsp;For Collage Use <span style="font-size:15px;font-weight: normal">(Upload In Software</span> <input type="text" name="input_1" style="width: 50px;height: 30px;"/> <span style="font-size: 17px;font-style: italic">Book No. </span><input type="text" name="input_1"  value="<?php echo $book_number ?>" style="width: 100px;height: 30px;border:none; font-size:20px;border-bottom: 1px solid black"> <span style="font-size: 17px;font-style: italic">Receipt No. </span><input type="text" name="input_1" value="<?php echo str_pad($i, 3, '0', STR_PAD_LEFT);?>" style="width: 100px;font-size:20px;height: 30px;border:none;border-bottom: 1px solid black"></h1>
            </div>
            <div style="margin-top:15px;">
                <div style="border: 1px solid black;width: 33%;float: left">
                    <h3 style="font-size: 18px;font-style: italic;text-align: center;margin-top:10px;">College Use</h3>
                    <h3 style="font-size: 15px;font-style: italic;padding-left: 10px;padding-top: 18px">Student Name:<input type="text" name="input_1" style="width: 140px;height: 13px;border:none;border-bottom: 1px solid black"></h3>
                    <h3 style="font-size: 15px;font-style: italic;padding-left: 10px;padding-top: 18px">Role No:<input type="text" name="input_1" style="width: 176px;height: 13px;border:none;border-bottom: 1px solid black"></h3>
                    <h3 style="font-size: 15px;font-style: italic;padding-left: 10px;padding-top: 18px">Date:<input type="text" name="input_1" style="width: 197px;height: 13px;border:none;border-bottom: 1px solid black"></h3>
                    <h3 style="font-size: 15px;font-style: italic;padding-left: 10px;padding-top: 18px">Amount:<input type="text" name="input_1" style="width: 175px;height: 13px;border:none;border-bottom: 1px solid black"></h3>
                    <h3 style="font-size: 15px;font-style: italic;padding-left: 10px;padding-top: 18px">Recived By:<input type="text" name="input_1" style="width: 155px;height: 13px;border:none;border-bottom: 1px solid black"></h3>
                    <h3 style="font-size: 15px;font-style: italic;padding-left: 10px;padding-top: 18px;padding-bottom: 10px">Sign:<input type="text" name="input_1" style="width: 197px;height: 13px;border:none;border-bottom: 1px solid black"></h3>
                </div>
                <div style="border: 1px solid black;width: 33%;float: left">
                    <h3 style="font-size: 18px;font-style: italic;text-align: center;margin-top:10px;">Control Center</h3>
                    <h3 style="font-size: 15px;font-style: italic;padding-left: 10px;padding-top: 18px">Fee Carry By:<input type="text" name="input_1" style="width: 140px;height: 13px;border:none;border-bottom: 1px solid black"></h3>
                    <h3 style="font-size: 15px;font-style: italic;padding-left: 10px;padding-top: 18px">Date:<input type="text" name="input_1" style="width: 197px;height: 13px;border:none;border-bottom: 1px solid black"></h3>
                    <h3 style="font-size: 15px;font-style: italic;padding-left: 10px;padding-top: 18px">Amount:<input type="text" name="input_1" style="width: 175px;height: 13px;border:none;border-bottom: 1px solid black"></h3>
                    <h3 style="font-size: 15px;font-style: italic;padding-top: 9px"><input type="text" name="input_1" style="width:100%;height: 2px;border:none;border-bottom: 1px solid black"></h3>
                    <h3 style="font-size: 18px;font-style: italic;text-align: center;padding-top: 5px">Fee Receive in Control Center</h3>
                    <h3 style="font-size: 15px;font-style: italic;padding-left: 10px;padding-top: 1px">Name:<input type="text" name="input_1" style="width: 190px;height: 13px;border:none;border-bottom: 1px solid black"></h3>
                    <h3 style="font-size: 15px;font-style: italic;padding-left: 10px;padding-top: 1px;">Date:<input type="text" name="input_1" style="width: 197px;height: 13px;border:none;border-bottom: 1px solid black"></h3>
                    <h3 style="font-size: 15px;font-style: italic;padding-left: 10px;padding-top: 1px;">Amount:<input type="text" name="input_1" style="width: 175px;height: 13px;border:none;border-bottom: 1px solid black"></h3>
                    <h3 style="font-size: 15px;font-style: italic;padding-left: 10px;padding-top: 1px;padding-bottom: 6px">Sign:<input type="text" name="input_1" style="width: 197px;height: 13px;border:none;border-bottom: 1px solid black"></h3>
                </div>
                <div style="border: 1px solid black;width: 33%;float: left">
                    <h3 style="font-size: 18px;font-style: italic;text-align: center;margin-top:10px;">For Bank</h3>
                    <h3 style="font-size: 15px;font-style: italic;padding-left: 10px;padding-top: 18px">Bank Name:<input type="text" name="input_1" style="width: 146px;height: 13px;border:none;border-bottom: 1px solid black"></h3>
                    <h3 style="font-size: 15px;font-style: italic;padding-left: 10px;padding-top: 18px">Account No:<input type="text" name="input_1" style="width: 146px;height: 13px;border:none;border-bottom: 1px solid black"></h3>
                    <h3 style="font-size: 15px;font-style: italic;padding-left: 10px;padding-top: 18px">Date:<input type="text" name="input_1" style="width: 197px;height: 13px;border:none;border-bottom: 1px solid black"></h3>
                    <h3 style="font-size: 15px;font-style: italic;padding-left: 10px;padding-top: 18px">Amount:<input type="text" name="input_1" style="width: 175px;height: 13px;border:none;border-bottom: 1px solid black"></h3>
                    <h3 style="font-size: 15px;font-style: italic;padding-left: 10px;padding-top: 18px">Submitted By:<input type="text" name="input_1" style="width: 140px;height: 13px;border:none;border-bottom: 1px solid black"></h3>
                    <h3 style="font-size: 15px;font-style: italic;padding-left: 10px;padding-top: 18px;padding-bottom: 10px">Sign:<input type="text" name="input_1" style="width: 197px;height: 13px;border:none;border-bottom: 1px solid black"></h3>
                </div>
            </div>
            <div style="border-bottom: 1px dashed black">&nbsp;</div>
            <div class="header">
                <div class="left logo">
                    <img src="<?php echo base_url();?>uploads/<?php echo $compuses->logo ?>" width="100%" alt="" />
                </div>
                <div class="left college_name text-center">
                    <h1 id="camp_name" style="text-transform: uppercase;color: #272579;font-weight: bolder;font-size: 40px;"><?php echo $compuses->campus_name; ?></h1>

                    <br />
                    <!--                        <p>Email : info@shahbazcollegeofpharmacy.edu.pk</p>-->
                </div>
            </div>
            <br>
            <br>
            <br>
            <br>
            <br>
            <br>
            <br>
            <br>
            <br>
            <div style="text-align: center">
                <p style="display: inline;font-size:15px;text-align: center"><i class="fas fa-mobile-alt"></i>  : <?php echo $compuses->phone; ?> </p> <p style="display: inline;font-size:15px"> <i class="fas fa-envelope-open-text"></i> : <?php echo $compuses->email; ?> </p> <p style="display: inline;font-size:15px"> <i class="fab fa-internet-explorer"></i> :  <?php echo $compuses->website; ?> </p>
            </div>
            <div style="margin-top: 10px">
                <div style="float: left;width: 69%">
                    <h1 style="font-size: 20px;font-style: italic;margin-top:8px">&nbsp;Name: <input type="text" name="input_1" style="width: 461px;height: 25px;border: 2px solid black;"/> </h1>
                    <h1 style="font-size: 20px;font-style: italic;margin-top:8px">&nbsp;Father Name: <input type="text" name="input_1" style="width: 400px;height: 25px;border: 2px solid black"/> </h1>
                    <h1 style="font-size: 20px;font-style: italic;margin-top:8px">&nbsp;Role No: <input type="text" name="input_1" style="width: 443px;height: 25px;border: 2px solid black"/> </h1>
                    <h1 style="font-size: 20px;font-style: italic;margin-top:8px">&nbsp;Date:  <input type="text" name="input_1" style="width: 50px;height: 25px;border: 2px solid black"/> <input type="text" name="input_1" style="width: 50px;height: 25px;border: 2px solid black"/> &nbsp; &nbsp; <input type="text" name="input_1" style="width: 50px;height: 25px;border: 2px solid black"/> <input type="text" name="input_1" style="width: 50px;height: 25px;border: 2px solid black"/> &nbsp;&nbsp; <input type="text" name="input_1" style="width: 50px;height: 25px;border: 2px solid black"/> <input type="text" name="input_1" style="width: 50px;height: 25px;border: 2px solid black"/> <input type="text" name="input_1" style="width: 50px;height: 25px;border: 2px solid black"/> <input type="text" name="input_1" style="width: 50px;height: 25px;border: 2px solid black"/> </h1>
                    <h1 style="font-size: 20px;font-style: italic;margin-top:8px">&nbsp;Class Name: <input type="text" name="input_1" style="width: 412px;height: 25px;border: 2px solid black"/> </h1>
                    <h1 style="font-size: 20px;font-style: italic;margin-top:8px">&nbsp;Cell Name: <input type="text" name="input_1" style="width: 423px;height: 25px;border: 2px solid black"/> </h1>
                    <h1 style="font-size: 16px;font-style: italic;margin-top:8px">&nbsp;Fee For College: <input type="text" name="input_1" style="width: 50px;height: 25px;border: 2px solid black"/> Supply: <input type="text" name="input_1" style="width: 50px;height: 25px;border: 2px solid black"/> Annual: <input type="text" name="input_1" style="width: 50px;height: 25px;border: 2px solid black"/> Other: <input type="text" name="input_1" style="width: 50px;height: 25px;border: 2px solid black"/> </h1>
                    <h1 style="font-size: 20px;font-style: italic;margin-top:8px">&nbsp;Amount: <input type="text" name="input_1" style="width: 165px;height: 25px;border: 2px solid black"/> Recived By: <input type="text" name="input_1" style="width: 170px;height: 25px;border: 2px solid black"/></h1>
                    <h1 style="font-size: 16px;font-style: italic;margin-top:8px">&nbsp;Fee Submited Through Chaque: <input type="text" name="input_1" style="width: 80px;height: 25px;border: 2px solid black"/> Cash: <input type="text" name="input_1" style="width: 80px;height: 25px;border: 2px solid black"/> </h1>
                    <h1 style="font-size: 16px;font-style: italic;margin-top:8px">&nbsp;Bank Name: <input type="text" name="input_1" style="width: 130px;height: 25px;border-bottom: 1px solid black!important;border:none;"/>&nbsp; Cheque Cash Date: <input type="text" name="input_1" style="width: 130px;height: 25px;border-bottom: 1px solid black!important;border:none;"/> </h1>
                    <h1 style="font-size: 16px;font-style: italic;margin-top:8px">&nbsp;Cheque No: <input type="text" name="input_1" style="width: 430px;height: 25px;border-bottom: 1px solid black!important;border:none;"/>&nbsp;</h1>

                </div>
                <div style="float: left;width: 29%">
                    <h1 style="font-size: 20px;font-style: italic;margin-top:8px">&nbsp;Book No: <input type="text" name="input_1" value="<?php echo $book_number ?>" style="font-size: 20px;width: 127px;height: 25px;border:none;border-bottom: 1px solid black"/> </h1>
                    <h1 style="font-size: 20px;font-style: italic;margin-top:8px">&nbsp;Receipt No: <input type="text" name="input_1" value="<?php echo str_pad($i, 3, '0', STR_PAD_LEFT);?>" style="font-size: 20px;width: 110px;height: 25px;border:none;border-bottom: 1px solid black"/> </h1>
                    <h1 style="font-size: 23px;font-style: italic;margin-top:8px">&nbsp; Fee Given By</h1>
                    <h1 style="font-size: 20px;font-style: italic;margin-top:8px">&nbsp;Name: <input type="text" name="input_1" style="width: 150px;height: 25px;border:none;border-bottom: 1px solid black"/> </h1>
                    <h1 style="font-size: 20px;font-style: italic;margin-top:8px">&nbsp;Sign: <input type="text" name="input_1" style="width: 160px;height: 25px;border:none;border-bottom: 1px solid black"/> </h1>
                    <h1 style="font-size: 20px;font-style: italic;margin-top:8px">&nbsp;Cell No: <input type="text" name="input_1" style="width: 135px;height: 25px;border:none;border-bottom: 1px solid black"/> </h1>
                    <div style="border:1px solid black;width:200px;height:130px;margin-top:8px;margin-left: 5px"></div>
                    <h1 style="font-size: 20px;font-style: italic;margin-top:5px">&nbsp;College Stamp </h1>
                </div>
            </div>
        </div>
        <br>
        <br>
        <br>
        <br>
        <br>
        <br>
        <div class="student_information" style="border-top:1px solid black">
            <br>
            <div style="float:left; display:inline; width:15%;text-align: right">
                <img src="<?php echo base_url();?>qr/<?php echo $book_number.'-'.$i?>.png" alt="" width="100" />
                <p style="    text-align: center;font-weight: bolder;font-size: 20px">Copy</p>
            </div>
            <div style="float:left; display:inline; width:85%;text-align: right">
						<p style="font-size: 12px">  فیس وصولی کے بعد نا قابل واپسی ہے    <i class="fas fa-star"></i></p>
                        <p style="font-size: 12px">  فیس جمع کروانے کے بعد رسید لینا لازمی ہے        <i class="fas fa-star"></i></p>
                        <p style="font-size: 12px">فیس جمع کروانے کے بعد ۸۰۴۲۹۷۷-۰۳۱۵ سے تصدیقی ایس ایم ایس موصول ہو گا اور طالب علم کے آن لا ئن پورٹل پر ظاہر ہو جا ئے گا<i class="fas fa-star"></i></p>
                        <p style="font-size: 12px">چیک کےذریعے جمع کروائی گئ فیس پر چیک کیش نہ ہونے کی صورت میں طالب علم کو500 سے 1000 تک کا جرمانہ یا کالج سے  کیا جا سکتا ہے  <i class="fas fa-star"></i></p>
                        <p style="font-size: 12px">Bank Al Habib For ATM or Bank Both 01210981003570018  بینک میں فیس جمع کروانےکیلئے <i class="fas fa-star"></i></p>
                        <p style="font-size: 12px;text-align: center"> مزید معلومات کیلئے رابطہ نمبر 03158042977</p>
                        <p style="font-size: 12px;text-align: center"> ‌اپنی‌ ‌فیس‌ ‌کا‌ ‌سٹیٹیس‌ ‌چیک‌ ‌کرنے‌ ‌کیلئے‌ ‌کیو‌ ‌آر‌ ‌کوڈ‌ ‌سکین‌ ‌کریں‌</p>
						<p style="font-size: 12px;text-align: center">نوٹ : دو ماہ تک فیس جمع نہ کروانے کی صورت میں آپ کو اگلے سال میں منتقل کر دیا جائے گا اور ایک ماہ مزید فیس جمع نہ کروانے کی صورت میں آپ کا نام کالج سے خارج کر دیا جائے گا‌</p>
            </div>
            <div style="clear:both;"></div>

        </div>
    </div>
    <div style="clear:both;"></div>
    <br>
    <br>
    <?php
}

?>

        <script>
            //  var number=document.getElementById("number").value;

            function myFunction() {
                var e = document.getElementById("cmp_get");
                var strUser = e.options[e.selectedIndex].text;

                for(var i = 1; i < 101; i++) {
                    // alert(strUser);
                    var a = "camp_name";
                    var b = i ;
                    var c = a+i;
                    // alert(c);
                    var myDiv = document.getElementById(c);
                    myDiv.innerHTML = strUser;
                    ///  alert(strUser);

                }

                var hid = document.getElementById("hide_div");
                var show = document.getElementById("show_div");
                hid.style.display = "none";
                show.style.display = "block";
            }

//            function ajax_call_for_book_no_store() {
//                var get_data_of_book = document.getElementById("book_number_get").value;
//                //alert(get_data_of_book);
//
//                jQuery.ajax({
//                    type: "post",
//                    async: false,
//                    url: '<?php //echo site_url();?>///AdvertisementDevices/ajax_request_to_store_book_number',
//                    data: {
//                        get_data_of_book : get_data_of_book,
//                    },
//                    success: function(data) {
//                        var show_hide = document.getElementById("show_div");
//                        show_hide.style.display = "none";
//                    }
//
//                });
//
//
//
//
//            }



        </script>

<script type="text/javascript">

    jQuery('.ajax_call_for_book_no_store').on('click',function(){
        var get_data_of_book = $("#book_number_get").val();
        var branch_code_get = $("#branch_code_get").val();
        var created_by_code_get = $("#created_by_code_get").val();
        jQuery.ajax({
            type: "post",
            async: false,
            url: '<?php echo site_url();?>/documents/ajax_request_to_store_book_number',
            data: {
                get_data_of_book : get_data_of_book,
                branch_code_get : branch_code_get,
                created_by_code_get : created_by_code_get,
            },
            success: function(data) {
               // jQuery('.campus_users').html(data);
                var show_hide = document.getElementById("show_div");
                show_hide.style.display = "none";
            }

        });
    });
</script>

	</body>
</html>