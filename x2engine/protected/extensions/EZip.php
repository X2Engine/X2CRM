<?php
class EZip extends CApplicationComponent {
	public function infosZip ($src, $data=true){
        if (($zip = zip_open(realpath($src))))
        {
            while (($zip_entry = zip_read($zip)))
            {
                $path = zip_entry_name($zip_entry);
                if (zip_entry_open($zip, $zip_entry, "r"))
                {
                    $content[$path] = array (
                        'Ratio' => zip_entry_filesize($zip_entry) ? round(100-zip_entry_compressedsize($zip_entry) / zip_entry_filesize($zip_entry)*100, 1) : false,
                        'Size' => zip_entry_compressedsize($zip_entry),
                        'NormalSize' => zip_entry_filesize($zip_entry));
                    if ($data)
                        $content[$path]['Data'] = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
                    zip_entry_close($zip_entry);
                }
                else
                    $content[$path] = false;
            }
            zip_close($zip);
            return $content;
        }
        return false;
    }
    
    public function extractZip ($src, $dest)
    {
        $zip = new ZipArchive;
        if ($zip->open($src)===true)
        {
            $zip->extractTo($dest);
            $zip->close();
            return true;
        }
        return false;
    }
    
    public function makeZip ($src, $dest)
    {
        $zip = new ZipArchive;
        $src = is_array($src) ? $src : array($src);
        if ($zip->open($dest, ZipArchive::CREATE) === true)
        {
            foreach ($src as $item)
                if (file_exists($item))
                    $this->addZipItem($zip, realpath(dirname($item)).'/', realpath($item).'/');
            $zip->close();
            return true;
        }
        return false;
    }
    
    private function addZipItem ($zip, $racine, $dir)
    {
        if (is_dir($dir))
        {
            $zip->addEmptyDir(str_replace($racine, '', $dir));
            $lst = scandir($dir);
                array_shift($lst);
                array_shift($lst);
            foreach ($lst as $item)
                $this->addZipItem($zip, $racine, $dir.$item.(is_dir($dir.$item)?'/':''));
        }
        elseif (is_file($dir))
            $zip->addFile($dir, str_replace($racine, '', $dir));
    }
}
?>