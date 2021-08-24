<?php

class DB
{

    private static $dbh, $sql, $res, $data;

    public function __construct()
    {
        self::$dbh = new PDO("mysql:host=localhost;dbname=community_blog", "root", "password");
        self::$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function query($params = [])
    {
        self::$res = self::$dbh->prepare(self::$sql);
        self::$res->execute($params);
    }

    public function get()
    {
        $this->query();
        self::$data = self::$res->fetchAll(PDO::FETCH_OBJ);
        return self::$data;
    }

    public function count()
    {
        $this->query();
        self::$data = self::$res->rowCount();
        return self::$data;
    }

    public function getOne()
    {
        $this->query();
        self::$data = self::$res->fetch(PDO::FETCH_OBJ);
        return self::$data;
    }

    public function orderBy($col, $order)
    {
        self::$sql .= " order by $col $order";
        return $this;
    }

    public static function table($table) // Cannot use $this in a static method
    {
        self::$sql = "select * from $table";
        $db = new self();
        return $db;
    }

    public function where($col, $operator, $val = "")
    {
        switch (func_num_args()) {
            case 2:
                self::$sql .= " where $col='$operator'";
                break;
            case 3:
                self::$sql .= " where $col $operator '$val'";
                break;
        }
        return $this;
    }

    public function andWhere($col, $operator, $val = "")
    {
        switch (func_num_args()) {
            case 2:
                self::$sql .= " and $col='$operator'";
                break;
            case 3:
                self::$sql .= " and $col $operator '$val'";
                break;
        }
        return $this;
    }

    public function orWhere($col, $operator, $val = "")
    {
        switch (func_num_args()) {
            case 2:
                self::$sql .= " or $col='$operator'";
                break;
            case 3:
                self::$sql .= " or $col $operator '$val'";
                break;
        }
        return $this;
    }

    public static function create($table, $data)
    {
        $cols = implode(',', array_keys($data));
        $values = "";
        $count = 1;
        foreach ($data as $d) {
            $values .= "?";
            if ($count < count($data)) {
                $values .= ",";
            }
            $count++;
        }
        self::$sql = "insert into $table ($cols) values ($values)";
        $db = new self();
        $db->query(array_values($data)); // Pass params to be bound to query method
        $lastInsertId = self::$dbh->lastInsertId(); // PDO has this method of retrieving last inserted ID
        return DB::table($table)->where('id', $lastInsertId)->getOne();
    }

    public static function update($table, $data, $id)
    {
        $count = 1;
        $updateString = "";
        foreach ($data as $key => $val) {
            $updateString .= "$key=?";
            if ($count < count($data)) {
                $updateString .= ",";
            }
            $count++;
        }
        self::$sql = "update $table set $updateString where id='$id'";
        $db = new self();
        $db->query(array_values($data));
        return DB::table($table)->where('id', $id)->getOne();
    }

    public static function delete($table, $id)
    {
        self::$sql = "delete from $table where id='$id'";
        $db = new self();
        $db->query();
        return true;
    }

    public function paginate($records_per_page = 5, $append = "")
    {
        // Get Total Records before paginate
        $totalRecords = $this->count();

        // Total Page Calculation
        $totalPages = (int) ceil($totalRecords / $records_per_page);
        $totalPages = $totalPages === 0 ? 1 : $totalPages;

        // Check GET params for page, also check out of bounds
        $page_no = isset($_GET['page']) ? $_GET['page'] : 1;
        $page_no = $page_no < 1 ? 1 : $page_no;
        $page_no = $page_no > $totalPages ? $totalPages : $page_no;

        // Paginate Query
        $startIndex = ($page_no - 1) * $records_per_page;
        self::$sql .= " limit $startIndex, $records_per_page";
        $data = $this->get();

        // Also check and add next and prev pages
        $next_no = $page_no == $totalPages ? 1 : $page_no + 1;
        $next_page = "?page=$next_no&$append";
        $prev_no = $page_no == 1 ? $totalPages : $page_no - 1;
        $prev_page = "?page=$prev_no&$append";

        // Build Return Array
        return [
            "data" => $data,
            "current_page_no" => $page_no,
            "total_pages" => $totalPages,
            "totalRecords" => $totalRecords,
            "prev_page" => $prev_page,
            "next_page" => $next_page
        ];
    }

    public static function raw($sql)
    {
        self::$sql = $sql;
        $db = new self();
        return $db;
    }
}
