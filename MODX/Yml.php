<?php

namespace Vendor\Yandex;

class Yml
{
    private function replaceSymbols(string $value): string
    {
        $value = str_replace("&", "&amp;", $value);
        $value = str_replace("\"", "&quot;", $value);
        $value = str_replace(">", "&gt;", $value);
        $value = str_replace("<", "&lt;", $value);
        $value = str_replace("'", "&apos;", $value);
        $value = str_replace("+", " ", $value);
        return $value;
    }

    private function textLower(string $value): string
    {
        $strb = mb_strtoupper($value);
        $strb = mb_substr($strb, 0, 1);
        $strm = mb_substr($value, 1);
        $strm = mb_strtolower($strm);
        $line = $strb . $strm;
        $text = $this->replaceSymbols(strip_tags($line));
        return $text;
    }

    private function getCategory($modx): string
    {
        $query = "SELECT id, pagetitle, parent FROM modx_site_content WHERE class_key='msCategory' ORDER BY menuindex";

        $sql = $modx->query($query);
        if ($sql && $sql->rowCount() > 0) {
            $data = $sql->fetchAll(\PDO::FETCH_ASSOC);
        }

        $categories = "";
        foreach ($data as $item) {
            $categories .= "<category id='" . $item['id'] . "' 
            parentId='" . ($item['parent'] == 0 ? 1 : $item['parent']) . "'>" . $item['pagetitle'] . "
            </category>\r\n";
        }
        return $categories;
    }

    private function shopProducts($modx): string
    {
        $items = "";
        $query = "SELECT c.id, c.uri, c.pagetitle, c.parent, c.introtext, p.article, p.price, p.made_in 
        FROM modx_site_content c Join modx_ms2_products p On c.id=p.id 
        WHERE c.class_key='msProduct' and c.published=1 ORDER BY c.menuindex";

        $sql = $modx->query($query);
        if ($sql && $sql->rowCount() > 0) {
            $data = $sql->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($data as $item) {
                $items .= "
                    <offer id='" . $item['id'] . "' available='true' type='vendor.model'>
                        <url>https://" . $_SERVER['HTTP_HOST'] . "/" . $item['uri'] . "</url>
                        <price>" . $item['price'] . "</price>
                        <currencyId>BYN</currencyId>
                        <categoryId>" . $item['parent'] . "</categoryId>
                        <picture>
                            https://" . $_SERVER['HTTP_HOST'] .
                            "/assets/ufiles/products/500/" . $item['article'] . ".jpg
                        </picture>
                        <model>" . $this->textLower($item['pagetitle']) . "</model>
                        <vendor>Yves Rocher</vendor>
                        <description>" . $this->textLower($item['introtext']) . "</description>
                        <sales_notes>Минимальная сумма заказа 19 белорусских рублей</sales_notes>
                        <country_of_origin>" . $this->textLower($item['made_in']) . "</country_of_origin>
                    </offer>";
            }
        }
        return $items;
    }

    public function viewYml($modx): string
    {
        $xml = "<?xml version='1.0' encoding='utf-8'?>
        <yml_catalog date='" . date('Y-m-d H:i') . "'>
        <shop>
        <name>Косметика Ив Роше</name>
        <company>Ив Роше</company>
        <url>http://" . $_SERVER['HTTP_HOST'] . "</url>
        <currencies>
            <currency id='BYN' rate='1' />
        </currencies>
        <categories>
            <category id='1' >Косметика</category>
            " . $this->getCategory($modx) . "
        </categories>
        <local_delivery_cost>0</local_delivery_cost>
        <offers>" . $this->shopProducts($modx) . "
        </offers>
        </shop>
        </yml_catalog>";

        echo $xml;
    }
}
