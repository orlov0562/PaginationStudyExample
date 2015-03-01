<?php

    $db = array(
        'host' => 'localhost',
        'user' => 'mysql',
        'pass' => 'mysql',
        'name' => 'pagination',
        'link' => null,
    );

    $pagination = array(
        'per_page' => 15,
    );

    // -------------------------------------

    function dbConnect(){
        global $db;
        $db['link'] = mysqli_connect($db['host'], $db['user'], $db['pass']);
        mysqli_select_db($db['link'], $db['name']);
    }

    function getTotalRecords() {
        global $db;
        $query = 'SELECT COUNT(`id`) FROM `records`';
        $result = mysqli_query($db['link'], $query);
        $row = mysqli_fetch_row($result);
        return intval($row[0]);
    }


    function getTotalPages() {
        global $pagination;
        $totalRecords = getTotalRecords();
        $totalPages = ceil( $totalRecords / $pagination['per_page'] ) - 1;
        return $totalPages;
    }

    function getRecords($currentPage) {
        global $db, $pagination;
        $offset = $currentPage * $pagination['per_page'];
        $query = 'SELECT * FROM `records` LIMIT '.$offset.', '.$pagination['per_page'];
        $result = mysqli_query($db['link'], $query);
        $ret = array();
        while ($row = mysqli_fetch_assoc($result)) $ret[] = $row;
        return $ret;
    }

    function dbFillInTableWithTestData() {
        global $db;
        $queryData = array();
        for ($i=0; $i<1000; $i++) {
            $time = rand(0,23).':'.rand(0,59).':'.rand(0,59);
            $price = rand(10, 100);
            $queryData[] = '("'.$time.'", "'.$price.'")';
        }

        $query = 'INSERT INTO `records` (`time`, `price`) VALUES '
                 .implode(', ',$queryData);
        mysqli_real_query($db['link'], $query);
    }

    function printPagination($total, $current) {
        echo '<ul class="pagination">';
        for ($i=0; $i<=$total; $i++) {
            if ( $i==$current ) {
                echo '<li class="current">'.($i+1).'</li>';
            } else {
                echo '<li><a href="?page='.$i.'">'
                     .($i+1).'</a></li>'
                ;
            }
        }

        if (($current+1)<=$total || ($current-1)>=0) {
            echo '<li>|</li>';
            if (($current-1)>=0) {
                echo '<li><a href="?page='.($current-1).'">Previous</a></li>';
            }
            if (($current+1)<=$total) {
                echo '<li><a href="?page='.($current+1).'">Next</a></li>';
            }
        }

        echo '</ul>';
    }

    // -------------------------------------

    dbConnect();

    $totalPages = getTotalPages();

    if (!$totalPages) {
        if (empty($_GET['r'])) {
            dbFillInTableWithTestData();
            Header('Location:?r=1');
        } else {
            die('Can\'t fillin table with test data');
        }
    }

    $currentPage = isset($_GET['page'])
                 ? intval($_GET['page'])
                 : 0
    ;

    // -------------------------------------

?>

<!DOCTYPE HTML>
<html>
<head>
	<title>Pagination example</title>
    <style type="text/css">
    <!--
        ul.pagination { overflow: auto; }
        ul.pagination li { float:left; padding:5px; display:inline-block; margin:2px;}
        ul.pagination li.current { font-weight:bold; background-color:black; color:white; }

        table th, table td {padding:3px;}
        table th {background-color: #22599D; color:white;}
        tr:nth-child(even) td {background: #CCCCCC}
        tr:nth-child(odd) td {background: #F0F0F0}
    -->
    </style>
</head>

<body>

<?php printPagination($totalPages, $currentPage); ?>

<hr />
<?php
    if ($records = getRecords($currentPage)) {
        echo '<table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Time</th>
                        <th>Price</th>
                    </tr>
                </thead>
                <tbody>';
        foreach($records as $record) {
            echo '<tr>
                    <td>'.$record['id'].'</td>
                    <td>'.$record['time'].'</td>
                    <td>'.$record['price'].'</td>
                  </tr>
            ';
        }
        echo '  <tbody>
            </table>
        ';
    }
?>
<hr />

<?php printPagination($totalPages, $currentPage); ?>

</body>
</html>