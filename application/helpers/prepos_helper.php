<?php

function Cleansing($text) 
{
  
  $str = preg_replace('/#([\w-]+)/i', '', $text); // @someone
  $str = preg_replace('/@([\w-]+):/i', '', $str); // #tag
  $str = preg_replace('/@([\w-]+)/i', '', $str); // #tag
  $str = preg_replace('/^RT/', '', $str); // #RT
  $str = preg_replace('/[^ -\x{2122}]\s+|\s*[^ -\x{2122}]/u', ' ', $str); //emoticon
  $str = preg_replace('/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/', '', $str); //url

  return $str;
}

function Case_Folding($text)
{
  return strtolower($text);
}

function Tokenizing($text)
{
  $strTkn = preg_replace("/[^a-z-?! ]/i", "", $text);
  $strTkn = preg_replace("/[\/\$?!]/i", "", $strTkn);
  return $strTkn;
}

function cekKamus($kata)
{
  $kata_dasar = base_url()."assets/kata_dasar.txt";
  // $kd = @fopen($kata_dasar, 'r');
  $kd = file_get_contents($kata_dasar);
  if ($kd) {
    $arrKataDasar = explode("\n", $kd);
    $arrKataDasar = array_map('trim', $arrKataDasar);
  }
  if (in_array($kata, $arrKataDasar)) {
    return true;
  } else {
    return false;
  }
}

function Del_Inflection_Suffixes($kata)
{
  $kataAsal = $kata;
  if (preg_match('/([km]u|nya|[kl]ah|pun|kan|tah)$/', $kata)) { // Cek Inflection Suffixes
    $__kata = preg_replace('/([km]u|nya|[kl]ah|pun|kan|tah)$/', '', $kata);
    return $__kata;
  }
  return $kataAsal;
}

function Cek_Prefix_Disallowed_Sufixes($kata)
{
  if (preg_match('/^(be)[[:alpha:]]+(i)$/', $kata)) { // be- dan -i
    return true;
  }

  if (preg_match('/^(se)[[:alpha:]]+(i|kan)$/', $kata)) { // se- dan -i,-kan
    return true;
  }
  return false;
}

function Del_Derivation_Suffixes($kata)
{
  $kataAsal = $kata;
  if (preg_match('/(i|an)$/', $kata)) { // Cek Suffixes
    $__kata = preg_replace('/(i|an)$/', '', $kata);
    if (cekKamus($__kata)) { // Cek Kamus
      return $__kata;
    }
  }
  return $kataAsal;
}

function Del_Derivation_Prefix($kata)
{
  $kataAsal = $kata;

  /* —— Tentukan Tipe Awalan ————*/
  if (preg_match('/^(ber|di|[ks]e)/', $kata)) { // Jika di-,ke-,se-
    $__kata = preg_replace('/^(ber|di|[ks]e)/', '', $kata);
    
    if (cekKamus($__kata)) {
      return $__kata; // Jika ada balik
    }
    $__kata__ = Del_Derivation_Suffixes($__kata);
    if (cekKamus($__kata__)) {
      return $__kata__;
    }
    /*————end “diper-”, ———————————————*/
    if (preg_match('/^(diper)/', $kata)) {
      $__kata = preg_replace('/^(diper)/', '', $kata);
      if (cekKamus($__kata)) {
        return $__kata; // Jika ada balik
      }
    }
    return $__kata;
    /*————end “diper-”, ———————————————*/
  }
  if (preg_match('/^([tmbp]e)/', $kata)) { //Jika awalannya adalah “te-”, “me-”, “be-”, atau “pe-”

  }
  /* — Cek Ada Tidaknya Prefik/Awalan (“di-”, “ke-”, “se-”, “te-”, “be-”, “me-”, atau “pe-”) ——*/
  if (preg_match('/^(di|[kstbmp]e)/', $kata) == FALSE) {
    return $kataAsal;
  }

  return $kataAsal;
}

function Nazief_Stemming($kata)
{

  $kataAsal = $kata;

  /* 1. Cek Kata di Kamus jika Ada SELESAI */
  if (cekKamus($kata)) { // Cek Kamus
    return $kata; // Jika Ada kembalikan
  }

  /* 2. Buang Infection suffixes (\-lah”, \-kah”, \-ku”, \-mu”, atau \-nya”) */
  $kata = Del_Inflection_Suffixes($kata);

  /* 3. Buang Derivation suffix (\-i” or \-an”) */
  $kata = Del_Derivation_Suffixes($kata);

  /* 4. Buang Derivation prefix */
  $kata = Del_Derivation_Prefix($kata);

  return $kata;
}
