<?php

namespace App\Actions\Legacy;

class LegacyImportStaticData
{
    public static function mediaPhotos(): array
    {
        return [
            ['src' => '/assets/img/media/Abraxas2.webp', 'alt' => 'Abraxas', 'caption' => 'Abraxas Club', 'date' => '2023-10-15'],
            ['src' => '/assets/img/media/Decadance1.webp', 'alt' => 'Decadance', 'caption' => 'Decadance Party', 'date' => '2023-09-20'],
            ['src' => '/assets/img/media/Decadance2_optimized.webp', 'alt' => 'Decadance 2', 'caption' => 'Decadance Night', 'date' => '2023-08-30'],
            ['src' => '/assets/img/media/Decadance3.webp', 'alt' => 'Decadance 3', 'caption' => 'Decadance Festival', 'date' => '2023-07-15'],
            ['src' => '/assets/img/media/PrideGranCanarias1.webp', 'alt' => 'Pride Gran Canarias', 'caption' => 'Pride Gran Canarias', 'date' => '2023-06-10'],
            ['src' => '/assets/img/media/PrideGranCanarias2.webp', 'alt' => 'Pride Gran Canarias 2', 'caption' => 'Pride Gran Canarias Festival', 'date' => '2023-05-20'],
            ['src' => '/assets/img/media/SitgesPride1.webp', 'alt' => 'Sitges Pride', 'caption' => 'Sitges Pride', 'date' => '2023-04-15'],
            ['src' => '/assets/img/media/SitgesPride2_optimized.webp', 'alt' => 'Sitges Pride 2', 'caption' => 'Sitges Pride Festival', 'date' => '2023-03-10'],
            ['src' => '/assets/img/media/SitgesPride4.webp', 'alt' => 'Sitges Pride 4', 'caption' => 'Sitges Pride Night', 'date' => '2023-02-20'],
            ['src' => '/assets/img/media/sitgesPride3.webp', 'alt' => 'Sitges Pride 3', 'caption' => 'Sitges Pride Party', 'date' => '2023-01-15'],
            ['src' => '/assets/img/media/Sixpaczone1.webp', 'alt' => 'Sixpaczone', 'caption' => 'Sixpaczone Event', 'date' => '2022-12-10'],
            ['src' => '/assets/img/media/Sixpaczone2_optimized.webp', 'alt' => 'Sixpaczone 2', 'caption' => 'Sixpaczone Party', 'date' => '2022-11-20'],
            ['src' => '/assets/img/media/Sixpaczone4.webp', 'alt' => 'Sixpaczone 4', 'caption' => 'Sixpaczone Night', 'date' => '2022-10-15'],
            ['src' => '/assets/img/media/privilege1.webp', 'alt' => 'Privilege', 'caption' => 'Privilege Club', 'date' => '2022-09-10'],
        ];
    }

    public static function mediaVideos(): array
    {
        return [
            ['id' => '3CJOX4v3hww', 'thumbnail' => 'https://i.ytimg.com/vi/3CJOX4v3hww/hqdefault.jpg', 'title' => 'sixpakzone2015videobajaryoutube com', 'date' => '2020-04-06', 'duration' => '1:35'],
            ['id' => 'L_NqmQPf1Mw', 'thumbnail' => 'https://i.ytimg.com/vi/L_NqmQPf1Mw/hqdefault.jpg', 'title' => 'CLUB MERCI CUMPLEAÑOS Dj JOBANI 2010', 'date' => '2010-11-02', 'duration' => '9:11'],
            ['id' => '0dSI9i1G3dc', 'thumbnail' => 'https://i.ytimg.com/vi/0dSI9i1G3dc/hqdefault.jpg', 'title' => 'UP THE MADS (MERCI)', 'date' => '2012-04-18', 'duration' => '6:11'],
            ['id' => 'kflSAKOMs70', 'thumbnail' => 'https://i.ytimg.com/vi/kflSAKOMs70/hqdefault.jpg', 'title' => 'CHIWAS @ L´ATLANTIDA AGOSTO 2009', 'date' => '2009-09-11', 'duration' => '5:40'],
            ['id' => 'pGlbwId_YI4', 'thumbnail' => 'https://i.ytimg.com/vi/pGlbwId_YI4/hqdefault.jpg', 'title' => "MR.CHIWAS @ L' ATLANTIDA", 'date' => '2011-08-26', 'duration' => '14:41'],
        ];
    }

    public static function partners(): array
    {
        return [
            ['slug' => 'parrots-group', 'name' => 'Parrots Group', 'url' => 'https://www.parrots-sitges.com/', 'logo_path' => '/assets/img/logos/parrots-group.png'],
            ['slug' => 'sitges-pride', 'name' => 'Sitges Pride', 'url' => 'https://sitgespride.com/', 'logo_path' => '/assets/img/logos/Sitges-Pride-Logo-2025-BLUE.png'],
            ['slug' => 'bears-week-sitges', 'name' => 'Bears Week Sitges', 'url' => 'https://bearssitges.org/bears-sitges-week/', 'logo_path' => '/assets/img/logos/LOGO-BEARS-WEEK-mini.png'],
            ['slug' => 'ibc-palm-springs', 'name' => 'IBC Palm Springs', 'url' => 'https://www.ibc-ps.com/', 'logo_path' => '/assets/img/logos/ibc+blue+logo+mk.webp'],
            ['slug' => 'bears-events', 'name' => 'Bears Events', 'url' => 'https://www.bearsevents.com/', 'logo_path' => '/assets/img/logos/Bear-Events-Logo-2-300x95.png'],
        ];
    }

    public static function socialLinks(): array
    {
        return [
            ['platform' => 'facebook', 'label' => 'Facebook', 'url' => 'https://www.facebook.com/fernandocardonatoro', 'icon_key' => 'facebook', 'location' => 'contact'],
            ['platform' => 'instagram', 'label' => 'Instagram', 'url' => 'https://www.instagram.com/mrchiloveyou/', 'icon_key' => 'instagram', 'location' => 'contact'],
            ['platform' => 'soundcloud', 'label' => 'SoundCloud', 'url' => 'https://soundcloud.com/mrchi1', 'icon_key' => 'soundcloud', 'location' => 'contact'],
            ['platform' => 'spotify', 'label' => 'Spotify', 'url' => 'https://open.spotify.com/', 'icon_key' => 'spotify', 'location' => 'contact'],
            ['platform' => 'facebook', 'label' => 'Facebook', 'url' => 'https://www.facebook.com/fernandocardonatoro', 'icon_key' => 'facebook', 'location' => 'footer'],
            ['platform' => 'instagram', 'label' => 'Instagram', 'url' => 'https://www.instagram.com/mrchiloveyou/', 'icon_key' => 'instagram', 'location' => 'footer'],
            ['platform' => 'soundcloud', 'label' => 'SoundCloud', 'url' => 'https://soundcloud.com/mrchi1', 'icon_key' => 'soundcloud', 'location' => 'footer'],
            ['platform' => 'spotify', 'label' => 'Spotify', 'url' => 'https://open.spotify.com/', 'icon_key' => 'spotify', 'location' => 'footer'],
        ];
    }

    public static function marqueeRows(): array
    {
        return [
            ['TechHouse', 'House', 'Hits', 'Contact', 'BearWeek', 'SitgesPride', 'TechHouse', 'Contacto', 'House', 'Hits', 'BearWeek', 'Contatto', 'SitgesPride', 'TechHouse', 'House', 'Kontakt', 'Hits', 'BearWeek', 'SitgesPride', 'Contact', 'TechHouse', 'House', 'Hits', 'Contacte', 'BearWeek', 'SitgesPride', 'TechHouse', 'Contact', 'House', 'Hits', 'BearWeek', 'Contacto', 'SitgesPride', 'TechHouse', 'House', 'Contatto', 'Hits', 'BearWeek', 'SitgesPride', 'TechHouse', 'Kontakt', 'House', 'Hits', 'Contact', 'BearWeek', 'SitgesPride', 'TechHouse', 'Contacto', 'House', 'Hits', 'BearWeek', 'Contatto', 'SitgesPride', 'TechHouse', 'House', 'Contacte', 'Hits', 'BearWeek', 'SitgesPride', 'TechHouse', 'Contact', 'House', 'Hits', 'BearWeek', 'Contacto', 'SitgesPride', 'TechHouse', 'House', 'Contatto', 'Hits', 'BearWeek', 'SitgesPride', 'TechHouse', 'Kontakt', 'House', 'Hits', 'Contact'],
            ['Electronic', 'Dance', 'Music', 'Contacter', 'Festival', 'Party', 'Electronic', 'Kontakti', 'Dance', 'Music', 'Festival', 'Liên-hệ', 'Party', 'Electronic', 'Dance', 'Επαφή', 'Music', 'Festival', 'Party', 'Контакт', 'Electronic', 'Dance', 'Music', '連絡', 'Festival', 'Party', 'Electronic', 'Contacter', 'Dance', 'Music', 'Festival', 'Kontakti', 'Party', 'Electronic', 'Dance', 'Liên-hệ', 'Music', 'Festival', 'Party', 'Electronic', 'Επαφή', 'Dance', 'Music', 'Festival', 'Контакт', 'Party', 'Electronic', 'Dance', 'Music', '連絡', 'Festival', 'Party', 'Electronic', 'Contacter', 'Dance', 'Music', 'Festival', 'Kontakti', 'Party', 'Electronic', 'Dance', 'Liên-hệ', 'Music', 'Festival', 'Party', 'Electronic', 'Επαφή', 'Dance', 'Music', 'Festival', 'Контакт', 'Party', 'Electronic', 'Dance', 'Music', '連絡', 'Festival', 'Party', 'Electronic', 'Contacter'],
            ['RadioChi', 'Beats', 'Vibes', 'संपर्क', 'Sound', 'Waves', 'RadioChi', 'اتصال', 'Beats', 'Vibes', 'Sound', 'Yhteystiedot', 'Waves', 'RadioChi', 'Beats', 'Kontakt', 'Vibes', 'Sound', 'Waves', 'Kapcsolat', 'RadioChi', 'Beats', 'Vibes', 'Kontak', 'Sound', 'Waves', 'RadioChi', 'संपर्क', 'Beats', 'Vibes', 'Sound', 'اتصال', 'Waves', 'RadioChi', 'Beats', 'Yhteystiedot', 'Vibes', 'Sound', 'Waves', 'RadioChi', 'Kontakt', 'Beats', 'Vibes', 'Sound', 'Kapcsolat', 'Waves', 'RadioChi', 'Beats', 'Vibes', 'Kontak', 'Sound', 'Waves', 'RadioChi', 'संपर्क', 'Beats', 'Vibes', 'Sound', 'اتصال', 'Waves', 'RadioChi', 'Beats', 'Yhteystiedot', 'Vibes', 'Sound', 'Waves', 'RadioChi', 'Kontakt', 'Beats', 'Vibes', 'Sound', 'Kapcsolat', 'Waves', 'RadioChi', 'Beats', 'Vibes', 'Kontak', 'Sound', 'Waves', 'RadioChi', 'संपर्क'],
        ];
    }
}
