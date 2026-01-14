<?php
require_once __DIR__ . '/BaseModel.php';

class SettingsModel extends BaseModel
{
    protected $table = 'site_settings';
    protected $primaryKey = 'id';

    /**
     * Obtenir tous les paramètres sous forme de tableau clé => valeur
     */
    public function getAllSettings(): array
    {
        $sql = "SELECT setting_key, setting_value FROM {$this->table}";
        $stmt = $this->db->query($sql);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $settings = [];
        foreach ($results as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        
        return $settings;
    }

    /**
     * Obtenir un paramètre spécifique
     */
    public function getSetting(string $key, $default = null)
    {
        $sql = "SELECT setting_value FROM {$this->table} WHERE setting_key = :key";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['key' => $key]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? $result['setting_value'] : $default;
    }

    /**
     * Mettre à jour ou créer un paramètre
     */
    public function setSetting(string $key, $value): bool
    {
        // Vérifier si le paramètre existe
        $existing = $this->getSetting($key);
        
        if ($existing !== null) {
            // Mettre à jour
            $sql = "UPDATE {$this->table} 
                    SET setting_value = :value, updated_at = NOW() 
                    WHERE setting_key = :key";
        } else {
            // Créer
            $sql = "INSERT INTO {$this->table} (setting_key, setting_value, setting_type) 
                    VALUES (:key, :value, 'text')";
        }
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'key' => $key,
            'value' => $value
        ]);
    }

    /**
     * Mettre à jour plusieurs paramètres
     */
    public function updateMultipleSettings(array $settings): bool
    {
        try {
            $this->db->beginTransaction();
            
            foreach ($settings as $key => $value) {
                $this->setSetting($key, $value);
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error updating settings: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtenir les paramètres de contact
     */
    public function getContactSettings(): array
    {
        $keys = [
            'contact_adresse',
            'contact_telephone',
            'contact_email',
            'contact_fax'
        ];
        
        $settings = [];
        foreach ($keys as $key) {
            $settings[$key] = $this->getSetting($key);
        }
        
        return $settings;
    }

    /**
     * Obtenir les paramètres des réseaux sociaux
     */
    public function getSocialSettings(): array
    {
        $keys = [
            'reseaux_facebook',
            'reseaux_twitter',
            'reseaux_linkedin',
            'reseaux_youtube'
        ];
        
        $settings = [];
        foreach ($keys as $key) {
            $settings[$key] = $this->getSetting($key);
        }
        
        return $settings;
    }

    /**
     * Obtenir le logo de l'université
     */
    public function getLogoPath(): ?string
    {
        return $this->getSetting('logo_universite');
    }

    /**
     * Obtenir les horaires d'ouverture
     */
    public function getOpeningHours(): ?string
    {
        return $this->getSetting('horaires_ouverture');
    }

    /**
     * Obtenir les paramètres SEO (titre, description, keywords)
     */
    public function getSEOSettings(): array
    {
        return [
            'site_title' => $this->getSetting('site_title'),
            'site_description' => $this->getSetting('site_description'),
            'site_keywords' => $this->getSetting('site_keywords'),
            'site_author' => $this->getSetting('site_author'),
            'site_favicon' => $this->getSetting('site_favicon'),
            'theme_color' => $this->getSetting('theme_color')
        ];
    }

    /**
     * Obtenir les paramètres Open Graph (réseaux sociaux)
     */
    public function getOpenGraphSettings(): array
    {
        return [
            'og_title' => $this->getSetting('og_title'),
            'og_description' => $this->getSetting('og_description'),
            'og_image' => $this->getSetting('og_image'),
            'site_url' => $this->getSetting('site_url')
        ];
    }
}