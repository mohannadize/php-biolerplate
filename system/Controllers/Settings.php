<?php

class Settings
{
    private static $DEFAULTS = [
		'logo_url' => "https://example.com/"
    ];

    private static $settings = [];

    public static function init()
    {
        self::loadSettings();
        foreach (self::$DEFAULTS as $key => $value) {
            if (!isset(self::$settings[$key])) {
                self::$settings[$key] = $value;
            }
        }
    }

    public static function get($key)
    {
        if (isset(self::$settings[$key])) {
            return self::$settings[$key];
        }
        return self::$DEFAULTS[$key];
    }

    public static function set($key, $value)
    {
        $query = DB::query("UPDATE settings SET value = ? WHERE key = ?", [$value, $key]);
        self::$settings[$key] = $value;
        return $query;
    }

    public static function loadSettings()
    {
        $settings = DB::query("SELECT * FROM settings");
        foreach ($settings as $setting) {
            self::$settings[$setting['key']] = json_decode($setting['value'], true);
        }
    }
}
