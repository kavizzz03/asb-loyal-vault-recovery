<?php
include 'db.php';

$customer = null;
$error = "";

if(isset($_POST['search']))
{
    $search = trim($_POST['search_text']);

    if(!empty($search))
    {
        $sql = "SELECT TOP 1
                    CM_CODE,
                    CM_TITLE,
                    CM_NAME,
                    CM_NIC,
                    CM_MOBILE,
                    CM_DOB,
                    CM_POINTS,
                    CM_ADD1,
                    CM_ADD2,
                    CM_ADD3,
                    CM_ADD4
                FROM M_TBLCUSTOMERS
                WHERE CM_CODE = ?
                   OR CM_MOBILE = ?
                   OR CM_NIC = ?";

        $params = array($search, $search, $search);

        $stmt = sqlsrv_query($conn, $sql, $params);

        if($stmt && sqlsrv_has_rows($stmt))
        {
            $customer = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        }
        else
        {
            $error = "Customer not found.";
        }
    }
}
?>deee
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>ASB Fashion Loyalty Portal</title>

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Segoe UI',sans-serif;
}

body{
    background:#f4f4f4;
}

.header{
    background:#c40000;
    color:#fff;
    text-align:center;
    padding:25px;
    font-size:32px;
    font-weight:bold;
    box-shadow:0 2px 10px rgba(0,0,0,0.2);
}

.sub-title{
    font-size:15px;
    margin-top:5px;
    opacity:0.9;
}

.container{
    max-width:1000px;
    margin:30px auto;
    padding:15px;
}

.search-box{
    background:#fff;
    padding:30px;
    border-radius:15px;
    box-shadow:0 0 15px rgba(0,0,0,.08);
}

.search-box h2{
    text-align:center;
    color:#c40000;
    margin-bottom:20px;
}

.search-form{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
}

.search-form input{
    flex:1;
    min-width:250px;
    padding:15px;
    border:1px solid #ddd;
    border-radius:10px;
    font-size:16px;
}

.search-form button{
    background:#c40000;
    color:white;
    border:none;
    padding:15px 30px;
    border-radius:10px;
    cursor:pointer;
    font-size:16px;
    font-weight:bold;
}

.search-form button:hover{
    background:#a30000;
}

.customer-card{
    margin-top:25px;
    background:#fff;
    border-radius:15px;
    overflow:hidden;
    box-shadow:0 0 15px rgba(0,0,0,.08);
}

.card-header{
    background:#c40000;
    color:#fff;
    padding:25px;
}

.card-header h2{
    margin-bottom:5px;
}

.card-body{
    padding:25px;
}

.row{
    display:flex;
    flex-wrap:wrap;
    border-bottom:1px solid #eee;
    padding:12px 0;
}

.label{
    width:220px;
    font-weight:bold;
    color:#c40000;
}

.value{
    flex:1;
}

.points-box{
    text-align:center;
    background:#fff5f5;
    border:2px solid #c40000;
    border-radius:15px;
    padding:20px;
    margin-bottom:20px;
}

.points-title{
    font-size:18px;
    color:#555;
}

.points{
    font-size:48px;
    font-weight:bold;
    color:#c40000;
}

.address{
    line-height:1.8;
}

.error{
    margin-top:20px;
    background:#ffe5e5;
    color:#c40000;
    padding:15px;
    border-radius:10px;
    text-align:center;
    font-weight:bold;
}

.footer{
    text-align:center;
    color:#777;
    padding:20px;
    margin-top:20px;
}

</style>
</head>

<body>

<div class="header">
    ASB Fashion Loyalty Customer Portal
    <div class="sub-title">
        Search by Customer Code, Mobile Number or NIC Number
    </div>
</div>

<div class="container">

    <div class="search-box">

        <h2>Customer Search</h2>

        <form method="post" class="search-form">

            <input
                type="text"
                name="search_text"
                placeholder="Enter Customer Code / Mobile Number / NIC"
                required>

            <button type="submit" name="search">
                Search
            </button>

        </form>

    </div>

<?php if($customer){ ?>

    <div class="customer-card">

        <div class="card-header">
            <h2>
                <?php echo htmlspecialchars($customer['CM_TITLE']." ".$customer['CM_NAME']); ?>
            </h2>
            <div>
                Loyalty Member
            </div>
        </div>

        <div class="card-body">

            <div class="points-box">
                <div class="points-title">ASB Fashion Loyalty Points</div>
                <div class="points">
                    <?php echo number_format($customer['CM_POINTS']); ?>
                </div>
            </div>

            <div class="row">
                <div class="label">Customer Code</div>
                <div class="value">
                    <?php echo htmlspecialchars($customer['CM_CODE']); ?>
                </div>
            </div>

            <div class="row">
                <div class="label">Customer Name</div>
                <div class="value">
                    <?php echo htmlspecialchars($customer['CM_TITLE']." ".$customer['CM_NAME']); ?>
                </div>
            </div>

            <div class="row">
                <div class="label">NIC Number</div>
                <div class="value">
                    <?php echo htmlspecialchars($customer['CM_NIC']); ?>
                </div>
            </div>

            <div class="row">
                <div class="label">Mobile Number</div>
                <div class="value">
                    <?php echo htmlspecialchars($customer['CM_MOBILE']); ?>
                </div>
            </div>

            <div class="row">
                <div class="label">Date of Birth</div>
                <div class="value">
                    <?php
                    if(!empty($customer['CM_DOB']))
                    {
                        echo $customer['CM_DOB']->format('d-m-Y');
                    }
                    ?>
                </div>
            </div>

            <div class="row">
                <div class="label">Address</div>
                <div class="value address">
                    <?php
                    echo htmlspecialchars($customer['CM_ADD1'])."<br>";
                    echo htmlspecialchars($customer['CM_ADD2'])."<br>";
                    echo htmlspecialchars($customer['CM_ADD3'])."<br>";
                    echo htmlspecialchars($customer['CM_ADD4']);
                    ?>
                </div>
            </div>

        </div>

    </div>

<?php } ?>

<?php if(!empty($error)){ ?>
    <div class="error">
        <?php echo $error; ?>
    </div>
<?php } ?>

</div>

<div class="footer">
    © <?php echo date('Y'); ?> ASB Fashion Loyalty Program
</div>

</body>
</html>