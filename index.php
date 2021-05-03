<?php
session_start();

class Website
{
    private $head;
    private $body;
    private $foot;

    public function __construct($title)
    {

        $this->head = '
        <!DOCTYPE html>
            <html lang="de">
                <head>
                <meta charset="utf-8">
                <meta name="viewport"content="width=device-width, initial-scale=1.0">
    <title>'.$title.'</title>
  </head><body>';

        $this->foot = "
</body></html>";

        $this->body = "";
    }

    public function getHtml()
    {
        return $this->head . $this->body . $this->foot;    
    } 
      
    public function test()
    {
        $filename = $_POST['testobject'].'.csv';
        
        if($_POST['api'] === 'secretAPIkey'){
            
            $record = $_POST['record'];
            
            $time =  $date = date('Y-m-d_H:i:s ', time());
          
            
            $handle = fopen($filename, 'a');
            
            $line = $time.','.$record.PHP_EOL;
            
            fwrite($handle, $line);
            
            fclose($handle);  
            
             
        }
        elseif($_SESSION['login'] === TRUE or $_GET['field'] === 'login_key')
        {
            $_SESSION['login'] = TRUE;
            $content = '';
            $verzeichnis = ".";
            $content .= "<ol>";


            if ( is_dir ( $verzeichnis )){
                if ( $handle = opendir($verzeichnis) ){
                    while (($file = readdir($handle)) !== false){
                        if (filetype( $file ) =='file' and pathinfo($file)['extension']=='csv'){
                            $content .= "<li>";
                            $content .= '
                                        <a href="./'.$file.'" target="_blank">'
                                            .$file.'
                                        </a> 
                                            size: '.(filesize($file)/1024).'
                                            kB, last update: '.date(DATE_RFC822, stat($file)['mtime'])."
                                        </li>\n";
                        }
                    }
                    closedir($handle);
                }
            }
            $content .= "</ol>";
            
            $this->body.='
            <style>
                .wrap { background: slateblue; }
                .spalte-1 { float: left; width: 49%; background: black; padding: 1em;}
                .spalte-2 { float: left; width: 1%; background: black; }
                .spalte-3 { float: left; width: 49%; background: grey; }
                body {
                    background-color: black;
                    color: lightgreen;
                }
            </style>
            <div class="wrap">
                <div class="spalte-1">
                <h1>LTE-Test</h1>
                 Diese Seite sammelt CSV-Daten eines automatisierten LTE-Testes.
                ';


            $this->body.='</div>

                <div class="spalte-3">
                    '.$content.' 
                </div>
                
            </div>
            
            ';
                  
        }
        else{

            $this->body .= "<form method='GET'> 
            <input name='field' type='password'>
            <br>
            <input type='submit' value='Login'>
            </form>" ;
        }
    
    }
}
$mySite = new Website("ltetest");

//$mySite->test();

$mySite->test();

echo $mySite->getHtml();

?>

