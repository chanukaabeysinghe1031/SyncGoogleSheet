<?php
require_once 'config.php';

read_sheet('15PTpze27r5kXChtp4CtoW6y95QmKvkTuB9cPrHymxQQ');

function read_sheet($spreadsheetId = '') {

    $client = new Google_Client();

    $db = new DB();
    $json = file_get_contents('token.json');
    $json_data = json_decode($json);
    $access_token = json_decode(json_encode($json_data->access_token));
    $expires_in = json_decode(json_encode($json_data->expires_in));

    $arr_token = (array) $db->get_access_token();

    echo $access_token."<br/>";
    echo $expires_in."<br/>";
    echo $arr_token['access_token']."<br/>";
    echo $arr_token['expires_in']."<br/>";

    $access = $arr_token['access_token'];
    $expires_in = $arr_token['expires_in'];
    $accessToken = array(
        'access_token' => $access,
        'expires_in' => $expires_in,
    );

    $client->setAccessToken($accessToken);

    $service = new Google_Service_Sheets($client);

    try {
        $range = 'A2:W15';
        $response = $service->spreadsheets_values->get($spreadsheetId, $range);
        $values = $response->getValues();

        if (empty($values)) {
            print "No data found.\n";
        } else {
            foreach ($values as $row) {
                // Print columns A and E, which correspond to indices 0 and 4.
                echo "<br/><br/><br/>******************************************************************************************************<br/>";
                $SKU = $row[0];
                $title =  $row[2];
                $description = $row[5];
                $category = $row[3];
                $size = $row[7];
                $color = $row[8];
                $MSRP = $row[10];
                $salePrice = $row[11];
                $wholeSalePrice = $row[12];
                $img1 = $row[13];
                $img2 = $row[14];
                $img3 = $row[15];

                echo "SKU : ".$SKU."<br/>.";
                echo "Title : ".$title."<br/>";
                echo "Category : ".$category."<br/>";
                echo "Description : ".$description."<br/>";

                $result = $db->findInProducts($title);
                if($result->num_rows>0){
                    echo "The product is already existing<br/>";
                    $productId= null;
                    while($row = $result->fetch_assoc()){
                        $productId = $row["id"];
                    }
                    $result2 = $db->findInVariants($SKU);
                    if($result2->num_rows>0){
                        echo "The variant is already existing. Updating the Variant.<br/>";
                        $db->updateVariant($SKU,$MSRP,$salePrice,$wholeSalePrice);
                    }else{
                        echo "No variant found. Inserting the variant<br/>";
                        $db->insertVariant($productId,$size,$color,$SKU,$MSRP,$salePrice,$wholeSalePrice);
                    }
                }else{
                    echo "No products found. Inserting into the products table<br/>";
                    $db->insertProduct($title,$description,$category);
                    echo "Inserting the variant<br/>";
                    $result3 = $db->findInProducts($title);
                    $productId= null;
                    while($row = $result3->fetch_assoc()){
                        $productId = $row["id"];
                    }
                    $db->insertVariant($productId,$size,$color,$SKU,$MSRP,$salePrice,$wholeSalePrice);
                    echo "Inserting the images<br/>";
                    $db->insertImage($productId,$img1,$img2,$img3);
                }
            }
        }
    } catch(Exception $e) {
        if( 401 == $e->getCode() ) {
            $refresh_token = $db->get_refersh_token();

            $client = new GuzzleHttp\Client(['base_uri' => 'https://accounts.google.com']);

            $json = file_get_contents('token.json');
            $json_data = json_decode($json);
            $refresh_token = json_decode(json_encode($json_data->refresh_token));

            $response = $client->request('POST', '/o/oauth2/token', [
                'form_params' => [
                    "grant_type" => "refresh_token",
                    "refresh_token" => $refresh_token,
                    "client_id" => GOOGLE_CLIENT_ID,
                    "client_secret" => GOOGLE_CLIENT_SECRET,
                ],
            ]);

            $data = (array) json_decode($response->getBody());
            $data['refresh_token'] = $refresh_token;

            $db->update_access_token(json_encode($data));

            read_sheet($spreadsheetId);
        } else {
            echo $e->getMessage(); //print the error just in case your data is not read.
        }
    }
}