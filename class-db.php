<?php
class DB {
    private $dbHost     = "localhost";
    private $dbUsername = "root";
    private $dbPassword = "";
    private $dbName     = "SyncGoogleSheet";

    public function __construct(){
        if(!isset($this->db)){
            // Connect to the database
            $conn = new mysqli($this->dbHost, $this->dbUsername, $this->dbPassword, $this->dbName);
            if($conn->connect_error){
                die("Failed to connect with MySQL: " . $conn->connect_error);
            }else{
                $this->db = $conn;
            }
        }
    }

    public function is_table_empty() {
        $result = $this->db->query("SELECT id FROM google_oauth WHERE provider = 'google'");
        if($result->num_rows) {
            return false;
        }

        return true;
    }

    public function get_access_token() {
        $sql = $this->db->query("SELECT provider_value FROM google_oauth WHERE provider = 'google'");
        $result = $sql->fetch_assoc();
        return json_decode($result['provider_value']);
    }

    public function get_refersh_token() {
        $result = $this->get_access_token();
        return $result->refresh_token;
    }

    public function update_access_token($token) {
        if($this->is_table_empty()) {
            $this->db->query("INSERT INTO google_oauth(provider, provider_value) VALUES('google', '$token')");
        } else {
            $this->db->query("UPDATE google_oauth SET provider_value = '$token' WHERE provider = 'google'");
        }
    }

    // To check whether the product is already existing
    public function findInProducts($title){
        $sql = "SELECT * FROM products WHERE title='".$title."'";
        $result = $this->db->query($sql);
        return $result;
    }

    // To check whether the variant is already exisiing
    public function findInVariants($SKU){
        $sql = "SELECT * FROM variants WHERE sku='".$SKU."'";
        $result = $this->db->query($sql);
        return $result;
    }

    // To insert product
    public function insertProduct($title,$description,$category){
        $date = date("Y.m.d h:i:s");
        $sql = 'INSERT INTO products (last_scraped,	title,description,collections) VALUES("'.$date.'","'.$title.'","'.$description.'","'.$category.'")';
        if ($this->db->query($sql) === TRUE) {
            echo "Successfully added product.<br>";
        } else {
            echo "Error updating record: " . $this->db->error."<br>";
        }
    }
    // To insert variant
    public function insertVariant($product_id,$variant_1,$variant_2,$sku,$price,$sale_price,$cost){
        $date = date("Y.m.d h:i:s");
        $price = ltrim($price, '$');
        $price = floatval($price);
        $sale_price = ltrim($sale_price, '$');
        $sale_price = floatval($sale_price);
        $cost = ltrim($cost, '$');
        $cost = floatval($cost);
        $sql = 'INSERT INTO variants (product_id,variant_1,variant_2,last_scraped,sku,stock,price,sale_price,cost) VALUES('.$product_id.',"'.$variant_1.'","'.$variant_2.'","'.$date.'","'.$sku.'",0,'.$price.','.$sale_price.','.$cost.')';
        if ($this->db->query($sql) === TRUE) {
            echo "Successfully added variant.<br>";
        } else {
            echo "Error updating record: " . $this->db->error."<br>";
        }
    }

    // To insert images
    public function insertImage($product_id,$image_1,$image_2,$image_3){

        if($image_1!=""&&$image_1!=null){
            $sql1 = 'INSERT INTO images (product_id,image) VALUES('.$product_id.',"'.$image_1.'")';
            if ($this->db->query($sql1) === TRUE) {echo "Successfully added image 1.<br>";}
            else {echo "Error inserting image 1record: " . $this->db->error."<br>";}
        }else{
            echo "Image 1 Not found.<br/>";
        }
        if($image_2!=""&&$image_2!=null){
            $sql2 = 'INSERT INTO images (product_id,image) VALUES('.$product_id.',"'.$image_2.'")';
            if ($this->db->query($sql2) === TRUE) {echo "Successfully added image 2.<br>";}
            else {echo "Error inserting image 2 record: " . $this->db->error."<br>";}
        }else{
            echo "Image 2 Not found.<br/>";
        }
        if($image_3!=""&&$image_3!=null){
            $sql3 = 'INSERT INTO images (product_id,image) VALUES('.$product_id.',"'.$image_3.'")';
            if ($this->db->query($sql3) === TRUE) {echo "Successfully added image 3.<br>";}
            else {echo "Error inserting image 3 record: " . $this->db->error."<br>";}
        }else{
            echo "Image 3 Not found.<br/>";
        }
    }

    // To update variant
    public function updateVariant($sku,$price,$sale_price,$cost){
        $price = ltrim($price, '$');
        $price = floatval($price);
        $sale_price = ltrim($sale_price, '$');
        $sale_price = floatval($sale_price);
        $cost = ltrim($cost, '$');
        $cost = floatval($cost);
        $sql = 'UPDATE variants SET  price='.$price.', sale_price='.$sale_price.', cost='.$cost.' WHERE sku="'.$sku.'"';
        if ($this->db->query($sql) === TRUE) {
            echo "Successfully updated variant.<br>";
        } else {
            echo "Error updating record: " . $this->db->error."<br>";
        }
    }
}