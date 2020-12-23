<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title></title>
    <?php
    class sql
    {
     public $conn;
     function __construct($dbname="")
     {
       $this->conn = new mysqli("localhost", "root", "", $dbname);
       if ($this->conn->connect_error)
       {
           die("Connection failed: " . $this->conn->connect_error);
       }
     }
     function query($sql)
     {
       $ret=[];
       $result = $this->conn->query($sql);
       if ($result->num_rows > 0) {
           // output data of each row
           while($row = $result->fetch_assoc()) {
               array_push($ret,$row);
           }
       }
       return $ret;
     }
     function run($sql)
     {
       $ret=[];
       $result = $this->conn->query($sql);
     }
     function close()
     {
       $conn->close();
     }
    }
     function ident($str,$lenght)
   	  {
         $count=$lenght-strlen($str);
         for ($j=0; $j < $count; $j++)
           $str.=' ';
         return $str;
       }
      function exists($database,$table)
      {
        $db=new sql();
        return count($db->query("SELECT TABLE_NAME AS tabela FROM INFORMATION_SCHEMA.TABLES
        WHERE TABLE_SCHEMA = '$database'
        AND  TABLE_NAME = '$table'"))==1;
      }
     ?>
    <style media="screen">
    *{
      background-color: #171717;
      color: white;
    }
    /*Spacing*/
      input,select,textarea,div.error, select option,label{
        padding: 10px;
        margin: 10px;
        border-radius: 5px;
        width: 100%;

      }
      label{

        font-size: 1.5em;
        display: block;
      }
      label::after {
        content: " : ";
      }
      /*Color*/
      input,select,textarea, select option{
        background: #292929;
        outline: none;
        border:1px solid white;
      }
      input{
        width: 44%;
        display: inline;
        margin-left: 4%;
      }
      input.solo{
        width: 100%;
        margin: 10px;
      }
      @media only screen and (max-width: 800px) {
        input{
          width: 100%;
          margin: 10px;
        }
      }
      input:active{
        background-color: #181818;
      }

      select{
        border-color: white;

      }
      div.error
      {
        color:red;
        font-size: 1.1em;
      }
      textarea{
        width: 98%;
      }
      input,select{
        font-size: 1.3em;
      }
      div.container{
        margin: auto;
        max-width: 1000px;
      }
    </style>
  </head>
  <body>
<div class="container">

    <form class="" action="" method="get">
      <label>Banco</label>
      <select class="" name="database">
        <?php
          $db=new sql();
          $r=$db->query('SHOW DATABASES');
          foreach ($r as $i):
          $ii=$i['Database'];
          ?>
          <option value="<?php echo  $ii?>" <?php if(isset($_GET['database']) && $_GET['database'] == $ii) echo "selected"?>><?php echo $ii ?></option>
        <?php endforeach; ?>
      </select>
      <?php
      if(isset($_GET['database'])):
        $db=new sql($_GET['database']);
        $r=$db->query("SHOW TABLES");
        ?>
        <label>Tabela</label>
        <select class="" name="table">
        <?php
        foreach ($r as $i):
          $ii=$i["Tables_in_$_GET[database]"];
          ?>
          <option value="<?php echo  $ii?>" <?php if(isset($_GET['table']) && $_GET['table'] == $ii) echo "selected"?>><?php echo $ii ?></option>
        <?php endforeach;?>
        </select>
      <?php endif; ?>
      <label>Opções</label>
      <?php if(isset($_GET['table'])&& exists($_GET['database'],$_GET['table'])): ?>
        <input type="submit" value="Selecionar">
        <input type="submit" name="action" value="Gerar modelo e controlador">
        <input type="submit" name="action" value="Adicionar campos de controle">
        <input type="submit" name="action" value="Código insert">
        <input type="submit" name="action" value="Código controlador">
        <input type="submit" name="action" value="Código select">
      <?php else: ?>

      <input class="solo" type="submit" value="Selecionar">
      <?php
      endif;
        function camel($data)
        {
          $ret='';
          foreach ($data as $i)
          {
            $ret.=ucfirst($i).'_';
          }
          $ret=substr($ret, 0, -1);
          return $ret;
        }
        ?>
        </form>
        <?php
        if(isset($_GET['action'])&&$_GET['action']=='Adicionar campos de controle')
        {

          $database = $_GET['database'];
          $table = $_GET['table'];
          $db=new sql($database);
          $result=$db->run("ALTER TABLE `$database`.`$table`
                            ADD COLUMN IF NOT EXISTS `created_by` INT NULL,
                            ADD COLUMN IF NOT EXISTS `created_at` DATETIME NULL,
                            ADD COLUMN IF NOT EXISTS `updated_by` INT NULL,
                            ADD COLUMN IF NOT EXISTS `updated_at` DATETIME NULL,
                            ADD COLUMN IF NOT EXISTS `excluido` TINYINT(1) NULL DEFAULT NULL;");
          $result=$db->run("ALTER TABLE `$database`.`$table`
                              ADD COLUMN IF NOT EXISTS `id` INT NOT NULL AUTO_INCREMENT,
                              ADD PRIMARY KEY (`id`);");
          $result=$db->run("ALTER TABLE `$database`.`$table`
                              ADD CONSTRAINT `FK_{$table}_created_by` FOREIGN KEY (`created_by`) REFERENCES `usuario` (`id`) ON UPDATE NO ACTION ON DELETE NO ACTION;");
          $result=$db->run("ALTER TABLE `$database`.`$table`
                              ADD CONSTRAINT `FK_{$table}_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `usuario` (`id`) ON UPDATE NO ACTION ON DELETE NO ACTION;");
        }
        ?>
        <?php
        if(isset($_GET['action'])&&$_GET['action']=='Gerar modelo e controlador'):
        $database= $_GET['database'];
        $table=$_GET['table'];
        $model = camel(explode('_',$table)).'_Model';
        $controler = ucfirst($table);

        $p_model = "application/models/$model.php";
        $p_controler = "application/controllers/$controler.php";
        if(!is_dir("application")):?><div class="error">Falha: Para gerar os arquivos, esta aplicação deve estar na pasta raiz de um sistema em code igniter.</div><?php
        elseif(file_exists($p_model)||file_exists($p_controler)):
          ?>
          <div class="error">Falha: Arquivo já existente.</div>
          <label>Saida</label>
          <textarea name="name" rows="8" cols="80"><?php
          echo "Possíveis rotas:\n";
          echo "public/$controler/get\n";
          echo "public/$controler/salvar\n";
          echo "\nArquivos encontrados:\n";
          echo "$p_model\n";
          echo "$p_controler\n";
          ?>
          </textarea>
          <label>Possível verifica token</label>
          <textarea name="name" rows="8" cols="80"><?php
          echo "'$controler' => array
(
  'get' => 0,
  'salvar' => 0,
),";
          ?></textarea>
          <?php
        else:

        $controler_str=
"<?php
defined('BASEPATH') OR exit('No direct script access allowed');

header(\"Access-Control-Allow-Origin: *\");
header(\"Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS\");
header(\"Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization\");

class $controler extends CI_Controller
{
    public function __construct()
      {
          // Sobrecarga no costrutor
          parent::__construct();

          // usado somente pra debugar
          //\$this->load->helper('chrome_helper');

          //Carrega os models necessários
          \$this->load->model('$model','$table');
      }


      public function get()
      {
          \$query = \$this->{$table}->get();


          \$retorno = array();
          \$retorno['status'] = 'erro';

          if (\$query) {
              \$retorno['status'] = 'sucesso';
              \$retorno['lista'] = \$query;
          }else{
              \$retorno['lista'] = [];
          }

          echo json_encode(\$retorno);
      }
      public function salvar()
      {
          \$status_code = 200;
          \$response = [
              'status' => 'sucesso',
              'lista'  => [],
          ];

          \$dados_insert = [];
          \$dados = json_decode(\$this->input->post('data'));


  ";
        $db=new sql($_GET['database']);
        $result=$db->query("DESC $_GET[table]");
        $controler_str.= "        \$dados_insert['$_GET[table]'] = [\n";
        foreach ($result as $i)
        {
          $ii=$i['Field'];
                if($ii=='id')         continue;
           else if($ii=='created_by') continue;
           else if($ii=='created_at') continue;
           else if($ii=='updated_by') continue;
           else if($ii=='updated_at') continue;
          $controler_str.= '           '.ident("'$ii' ",70)."=> \$dados->$ii,\n";
        }
        $controler_str.= '        ];';
        $controler_str.=
         "

         \$header = (object) \$this->input->request_headers();
         \$user = (object) \$this->jwt->decode(isset(\$header->authorization) ? \$header->authorization : \$header->Authorization, CONSUMER_KEY);
         \$dados_insert['id_usuario']=\$user->id_usuario;

         if ((int)\$dados->id == 0)
         {
           \$dados_insert['$table']['created_by'] = \$user->id_usuario;
           \$dados_insert['$table']['created_at'] = date('Y-m-d H:i:s', time());
           \$result = \$this->{$table}->salvar(\$dados_insert);
         }
         else
         {
           \$dados_insert['$table']['updated_by'] = \$user->id_usuario;
           \$dados_insert['$table']['updated_at'] = date('Y-m-d H:i:s', time());
           \$result = \$this->{$table}->atualizar(\$dados_insert, \$dados->id);
         }


        if (\$result)
        {
            \$response['lista']  = \$this->{$table}->get(\$result);
        }
         else
        {
            \$response = [
               'status' => 'erro',
               'lista'  => [],
           ];
        }

        return \$this->output
            ->set_content_type('application/json')
            ->set_status_header(\$status_code)
            ->set_output(
                json_encode(\$response)
            );
    }
}
?>";
      $model_str="
<?php

class $model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }
    public function get(\$id = null)
    {
          if(\$id != null)\$id = \"WHERE $table.id = \$id\";
          \$query = \$this->db->query(\"";
        function loop($database,$table,&$join,&$select)
        {
          $info=new sql("information_schema");
          $db=new sql($database);
          $fks=$info->query("SELECT K.COLUMN_NAME coluna,k.REFERENCED_TABLE_NAME tabela, k.REFERENCED_COLUMN_NAME chave
          FROM information_schema.TABLE_CONSTRAINTS i
          LEFT JOIN information_schema.KEY_COLUMN_USAGE k ON i.CONSTRAINT_NAME = k.CONSTRAINT_NAME
          WHERE i.CONSTRAINT_TYPE = 'FOREIGN KEY'
          AND i.TABLE_SCHEMA = '$database'
          AND i.TABLE_NAME = '$table'
          GROUP BY coluna;");
          $table=$_GET['table'];
          $fields="";
          foreach ($fks as $i) {
                   if($i['coluna']=='created_by') continue;
              else if($i['coluna']=='updated_by') continue;
              $ct=$i['tabela'];
              $fields=$db->query("DESC $ct");
              $fk_name=str_replace("id_","",$i['coluna']);
              foreach ($fields as $j) {
                $jj=$j['Field'];
                if($i['chave']==$jj)continue;
                $select.='                '.ident("$fk_name.$jj",70)." AS {$jj}_$fk_name,\n";
              }

              $join.="                LEFT JOIN $ct";
              if($ct != $fk_name)$join.=" AS $fk_name";
              $join.=" ON $table.$i[coluna] = $fk_name.$i[chave]\n";
          }

          // foreach ($fks as $i) {
          //     loop($database,$i['tabela'],$join,$select);
          // }

        }
        $join='';
        $select=",\n";
        loop($database,$table,$join,$select);
        if($select==",\n")$select='';
        $select=substr($select, 0, -2)."\n";
        $model_str.= "SELECT\n                $table.*{$select}                FROM $table\n$join                \$id";

        $model_str.="
          \");
          return \$query->result_object();
    }
    public function salvar(\$dados)
    {
        \$this->db->trans_begin();

        if (\$this->db->insert('$table', \$dados['$table']))
        {
            //INSERINDO
            \$id = \$this->db->insert_id();

            \$dados_log = array(
                'id_registro' => \$id,
                'tabela' => '$table',
                'acao' => 1,
                'sql' => str_replace(\"`\", \"\", \$this->db->last_query()),
                'data_cadastro' => date('Y-m-d H:i:s', time()),
                'id_usuario' => \$dados['id_usuario'],
            );

            \$this->auditoria->salvar(\$dados_log);

            if (\$this->db->trans_status() === false) {
                \$this->db->trans_rollback();
                return false;
            } else {
                \$this->db->trans_commit();
                return \$id;
            }
        }
    }
    public function atualizar(\$dados, \$id)
    {
        if (\$dados != null) {
            \$this->db->trans_begin();

            \$this->db->where('id', \$id);
            if (\$this->db->update('$table', \$dados['$table'])) {
                //Log
                \$dados_log = [
                    'id_registro'   => \$id,
                    'tabela'        => '$table',
                    'acao'          => 2,
                    'sql'           => str_replace(\"`\", \"\", \$this->db->last_query()),
                    'data_cadastro' => date('Y-m-d H:i:s', time()),
                    'id_usuario'    => \$dados['id_usuario']
                ];

                \$this->auditoria->salvar(\$dados_log);
            }
        }

        if (\$this->db->trans_status() === false) {
            \$this->db->trans_rollback();
            return false;
        } else {
            \$this->db->trans_commit();
            return \$id;
        }
    }
}
?>"

;
   function fwrite_long($file,$content)
   {
     $pieces = str_split($content, 1024 * 4);
     foreach ($pieces as $piece)
     {
       fwrite($file, $piece, strlen($piece));
     }
   }
    $f_controler = fopen($p_controler,'w');
    fwrite_long($f_controler, $controler_str);
    fclose($f_controler);
    $f_model=fopen($p_model,'w');
    fwrite_long($f_model, $model_str);
    fclose($f_model);
    ?>
    <label>Saida</label>
    <textarea name="name" rows="8" cols="80"><?php
    echo "Rotas:\n";
    echo "public/$controler/get\n";
    echo "public/$controler/salvar\n";
    echo "\nArquivos gerados:\n";
    echo "$p_model\n";
    echo "$p_controler\n";
    ?>
    </textarea>
    <label>Verifica token</label>
    <textarea name="name" rows="8" cols="80"><?php
    echo "'$controler' => array
(
  'get' => 0,
  'salvar' => 0,
),";
    ?></textarea>
    <?php
    endif;
    endif;
        ?>

        <?php
        if(isset($_GET['table']) && exists($_GET['database'],$_GET['table'])):
          ?>

          <?php if(isset($_GET['action'])&&$_GET['action']=='Código insert'):?>
          <label>Insert</label>
            <textarea name="name" rows="40" cols="200" spellcheck="false"><?php
              if(isset($_GET['database'])&&isset($_GET['table']))
              {
                $database=$_GET['database'];
                $table=$_GET['table'];

                $db=new sql($database);
                $result=$db->query("DESC $_GET[table]");
                $ret="{\n";

                  $info=new sql("information_schema");
                  $db=new sql($database);
                  $fks=$info->query("SELECT K.COLUMN_NAME coluna,k.REFERENCED_TABLE_NAME tabela, k.REFERENCED_COLUMN_NAME chave
                  FROM information_schema.TABLE_CONSTRAINTS i
                  LEFT JOIN information_schema.KEY_COLUMN_USAGE k ON i.CONSTRAINT_NAME = k.CONSTRAINT_NAME
                  WHERE i.CONSTRAINT_TYPE = 'FOREIGN KEY'
                  AND i.TABLE_SCHEMA = '$database'
                  AND i.TABLE_NAME = '$table'
                  GROUP BY coluna;");

                $ret="INSERT INTO $_GET[table]\n(\n";
                foreach ($result as $i) {
                  $ii=$i['Field'];
                  $ret.="   $ii,\n";
                }
                $ret=substr($ret, 0, -2);
                $ret.="\n)\nVALUES\n(\n";
                foreach ($result as $i)
                {
                  $ii=$i['Field'];
                  $ij=$i['Type'];
                  $str="SQL-$ij";
                       if(!(strpos($str, 'varchar')     === false)) $str='""';
                  else if(!(strpos($str, 'tinyint(1)')  === false)) $str='false';
                  else if(!(strpos($str, 'text')        === false)) $str='""';
                  else if(!(strpos($str, 'int')         === false)) $str='0';
                  else if(!(strpos($str, 'float')       === false)) $str='0.0';
                  else if(!(strpos($str, 'decimal')     === false)) $str='0.0';
                  else if(!(strpos($str, 'double')      === false)) $str='0.0';
                  else if(!(strpos($str, 'datetime')    === false)) $str='"2020-11-24 00:00:00.000"';
                  else if(!(strpos($str, 'date')        === false)) $str='"2020-11-24"';
                  foreach ($fks as $j) {
                    if($j['coluna']==$ii) $str='null';
                  }
                  $ret.="   $str,\n";
                }
                $ret=substr($ret, 0, -2);
                $ret.= "\n)\n";
                echo $ret;
              }
              ?></textarea>
            <?php elseif(isset($_GET['action'])&&$_GET['action']=='Código controlador'):?>
              <label>Controlador</label>
              <textarea name="name" rows="40" cols="200" spellcheck="false"><?php


                  $db=new sql($_GET['database']);
                  $result=$db->query("DESC $_GET[table]");
                  echo "\$dados_insert['$_GET[table]'] = [\n";
                  foreach ($result as $i)
                  {
                    $ii=$i['Field'];
                          if($ii=='id')         continue;
                     else if($ii=='created_by') continue;
                     else if($ii=='created_at') continue;
                     else if($ii=='updated_by') continue;
                     else if($ii=='updated_at') continue;
                    echo ident("   '$ii' ",70)."=> \$dados->$ii,\n";
                  }
                  echo '];';
                  echo "

\$header = (object) \$this->input->request_headers();
\$user = (object) \$this->jwt->decode(isset(\$header->authorization) ? \$header->authorization : \$header->Authorization, CONSUMER_KEY);
\$dados_insert['id_usuario']=\$user->id_usuario;

if ((int)\$dados->id == 0)
{
  \$dados_insert['$_GET[table]']['created_by'] = \$user->id_usuario;
  \$dados_insert['$_GET[table]']['created_at'] = date('Y-m-d H:i:s', time());
  \$result = \$this->$_GET[table]->salvar(\$dados_insert);
}
else
{
  \$dados_insert['$_GET[table]']['updated_by'] = \$user->id_usuario;
  \$dados_insert['$_GET[table]']['updated_at'] = date('Y-m-d H:i:s', time());
  \$result = \$this->$_GET[table]->atualizar(\$dados_insert, \$dados->id);
}

if (\$result)
{
  \$response['lista']  = \$this->$_GET[table]->get(\$result);
}
else
{
  \$response = [
  'status' => 'erro',
  'lista'  => [],
  ];
}
          ";

                ?></textarea>
              <?php elseif(isset($_GET['action'])&&$_GET['action']=='Código select'):?>
                <label>Select</label>
                <textarea name="name" rows="40" cols="200" spellcheck="false"><?php
                  function loop($database,$table,&$join,&$select)
                  {
                    $info=new sql("information_schema");
                    $db=new sql($database);
                    $fks=$info->query("SELECT K.COLUMN_NAME coluna,k.REFERENCED_TABLE_NAME tabela, k.REFERENCED_COLUMN_NAME chave
                    FROM information_schema.TABLE_CONSTRAINTS i
                    LEFT JOIN information_schema.KEY_COLUMN_USAGE k ON i.CONSTRAINT_NAME = k.CONSTRAINT_NAME
                    WHERE i.CONSTRAINT_TYPE = 'FOREIGN KEY'
                    AND i.TABLE_SCHEMA = '$database'
                    AND i.TABLE_NAME = '$table'
                    GROUP BY coluna;");
                    $table=$_GET['table'];
                    $fields="";
                    foreach ($fks as $i) {
                             if($i['coluna']=='created_by') continue;
                        else if($i['coluna']=='updated_by') continue;
                        $ct=$i['tabela'];
                        $fields=$db->query("DESC $ct");
                        $fk_name=str_replace("id_","",$i['coluna']);
                        foreach ($fields as $j) {
                          $jj=$j['Field'];
                          if($i['chave']==$jj)continue;
                          $select.=ident("$fk_name.$jj",70)." AS {$jj}_$fk_name,\n";
                        }

                        $join.="LEFT JOIN $ct";
                        if($ct != $fk_name)$join.=" AS $fk_name";
                        $join.=" ON $table.$i[coluna] = $fk_name.$i[chave]\n";
                    }

                    // foreach ($fks as $i) {
                    //     loop($database,$i['tabela'],$join,$select);
                    // }

                  }
                  $join='';
                  $select=",\n";
                  loop($_GET['database'],$_GET['table'],$join,$select);
                  if($select==",\n")$select='';
                  $select=substr($select, 0, -2)."\n";
                  echo "SELECT\n$_GET[table].*{$select}FROM $_GET[table]\n$join";
                ?></textarea>
              <?php
              endif
              ?>
              <label>JSON</label>
              <textarea name="name" rows="40" cols="200" spellcheck="false"><?php
                if(isset($_GET['database'])&&isset($_GET['table']))
                {
                  $database=$_GET['database'];
                  $table=$_GET['table'];

                  $db=new sql($database);
                  $result=$db->query("DESC $_GET[table]");
                  $ret="{\n";

                    $info=new sql("information_schema");
                    $db=new sql($database);
                    $fks=$info->query("SELECT K.COLUMN_NAME coluna,k.REFERENCED_TABLE_NAME tabela, k.REFERENCED_COLUMN_NAME chave
                    FROM information_schema.TABLE_CONSTRAINTS i
                    LEFT JOIN information_schema.KEY_COLUMN_USAGE k ON i.CONSTRAINT_NAME = k.CONSTRAINT_NAME
                    WHERE i.CONSTRAINT_TYPE = 'FOREIGN KEY'
                    AND i.TABLE_SCHEMA = '$database'
                    AND i.TABLE_NAME = '$table'
                    GROUP BY coluna;");
                  foreach ($result as $i)
                  {
                    $ii=$i['Field'];
                    $ij=$i['Type'];
                         if($ii=='created_by') continue;
                    else if($ii=='created_at') continue;
                    else if($ii=='updated_by') continue;
                    else if($ii=='updated_at') continue;
                    $str="SQL-$ij";
                         if(!(strpos($str, 'varchar')     === false)) $str='""';
                    else if(!(strpos($str, 'tinyint(1)')  === false)) $str='false';
                    else if(!(strpos($str, 'text')        === false)) $str='""';
                    else if(!(strpos($str, 'int')         === false)) $str='0';
                    else if(!(strpos($str, 'float')       === false)) $str='0.0';
                    else if(!(strpos($str, 'decimal')     === false)) $str='0.0';
                    else if(!(strpos($str, 'double')      === false)) $str='0.0';
                    else if(!(strpos($str, 'datetime')    === false)) $str='"2020-11-24 00:00:00.000"';
                    else if(!(strpos($str, 'date')        === false)) $str='"2020-11-24"';
                    foreach ($fks as $j) {
                      if($j['coluna']==$ii) $str='null';
                    }
                    $ret.=ident("   \"$ii\" ",70).": $str,\n";
                  }
                  $ret=substr($ret, 0, -2);
                  $ret.= "\n}";
                  echo $ret;
                }
                ?>
              </textarea>
              <?php
              endif;
                  ?>
  </body>
  </div>
</html>
