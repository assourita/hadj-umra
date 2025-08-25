-- Script d'initialisation PostgreSQL pour Omra Himra
-- Ce script est exécuté automatiquement lors de la création du conteneur

-- Activer les extensions nécessaires
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pg_trgm";
CREATE EXTENSION IF NOT EXISTS "unaccent";

-- Créer des index pour améliorer les performances
-- Ces index seront créés après les migrations Doctrine

-- Fonction pour générer des slugs
CREATE OR REPLACE FUNCTION generate_slug(input_text TEXT)
RETURNS TEXT AS $$
DECLARE
    result TEXT;
BEGIN
    -- Convertir en minuscules et remplacer les espaces par des tirets
    result := lower(trim(input_text));
    result := regexp_replace(result, '[^a-z0-9\s-]', '', 'gi');
    result := regexp_replace(result, '\s+', '-', 'g');
    result := regexp_replace(result, '-+', '-', 'g');
    result := trim(result, '-');
    
    RETURN result;
END;
$$ LANGUAGE plpgsql;

-- Fonction pour calculer la distance entre deux points GPS (pour futures fonctionnalités)
CREATE OR REPLACE FUNCTION calculate_distance(
    lat1 FLOAT, lon1 FLOAT, 
    lat2 FLOAT, lon2 FLOAT
) RETURNS FLOAT AS $$
DECLARE
    R CONSTANT FLOAT := 6371; -- Rayon de la Terre en km
    dLat FLOAT;
    dLon FLOAT;
    a FLOAT;
    c FLOAT;
BEGIN
    dLat := radians(lat2 - lat1);
    dLon := radians(lon2 - lon1);
    
    a := sin(dLat/2) * sin(dLat/2) + 
         cos(radians(lat1)) * cos(radians(lat2)) * 
         sin(dLon/2) * sin(dLon/2);
    
    c := 2 * atan2(sqrt(a), sqrt(1-a));
    
    RETURN R * c;
END;
$$ LANGUAGE plpgsql;

-- Configuration des paramètres PostgreSQL pour optimiser les performances
ALTER SYSTEM SET shared_preload_libraries = 'pg_stat_statements';
ALTER SYSTEM SET log_statement = 'all';
ALTER SYSTEM SET log_duration = on;
ALTER SYSTEM SET log_min_duration_statement = 1000; -- Log queries > 1s

-- Créer un utilisateur en lecture seule pour les rapports (optionnel)
-- CREATE USER omra_readonly WITH PASSWORD 'readonly_password';
-- GRANT CONNECT ON DATABASE omra_himra_db TO omra_readonly;
-- GRANT USAGE ON SCHEMA public TO omra_readonly;
-- GRANT SELECT ON ALL TABLES IN SCHEMA public TO omra_readonly;
-- ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT SELECT ON TABLES TO omra_readonly;

-- Message de confirmation
DO $$
BEGIN
    RAISE NOTICE 'Base de données Omra Himra initialisée avec succès !';
    RAISE NOTICE 'Extensions activées : uuid-ossp, pg_trgm, unaccent';
    RAISE NOTICE 'Fonctions créées : generate_slug, calculate_distance';
END $$; 