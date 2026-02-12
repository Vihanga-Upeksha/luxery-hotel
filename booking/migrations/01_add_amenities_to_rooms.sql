-- Migration: Add amenities and gallery images support to rooms table
-- This script adds new columns to support detailed room information

USE hotel_booking;

-- Add amenities column (JSON format) if it doesn't exist
ALTER TABLE rooms ADD COLUMN IF NOT EXISTS amenities JSON DEFAULT NULL COMMENT 'JSON array of amenities {"name": "icon", ...}';

-- Add gallery images column if it doesn't exist
ALTER TABLE rooms ADD COLUMN IF NOT EXISTS gallery_images JSON DEFAULT NULL COMMENT 'JSON array of image URLs';

-- Add room capacity description if it doesn't exist
ALTER TABLE rooms ADD COLUMN IF NOT EXISTS size_sqm INT DEFAULT NULL COMMENT 'Room size in square meters';

-- Add features/features if it doesn't exist
ALTER TABLE rooms ADD COLUMN IF NOT EXISTS features TEXT DEFAULT NULL COMMENT 'Key features of the room';

-- Update existing rooms with amenities (if not already set)
UPDATE rooms 
SET amenities = JSON_ARRAY(
    JSON_OBJECT('name', 'Free Wi-Fi', 'icon', 'fa-wifi'),
    JSON_OBJECT('name', 'Air Conditioning', 'icon', 'fa-wind'),
    JSON_OBJECT('name', '24/7 Room Service', 'icon', 'fa-concierge-bell'),
    JSON_OBJECT('name', 'Premium Toiletries', 'icon', 'fa-spa')
)
WHERE amenities IS NULL OR JSON_LENGTH(amenities) = 0;

-- Update existing rooms with gallery images
UPDATE rooms 
SET gallery_images = JSON_ARRAY(
    image_url,
    CONCAT('images/room-', LOWER(REPLACE(name, ' ', '-')), '-2.jpg'),
    CONCAT('images/room-', LOWER(REPLACE(name, ' ', '-')), '-3.jpg')
)
WHERE gallery_images IS NULL OR JSON_LENGTH(gallery_images) = 0;

-- Update room sizes if not set
UPDATE rooms SET size_sqm = 45 WHERE name = 'Deluxe Ocean View' AND size_sqm IS NULL;
UPDATE rooms SET size_sqm = 70 WHERE name = 'Executive Suite' AND size_sqm IS NULL;
UPDATE rooms SET size_sqm = 100 WHERE name = 'Family Retreat' AND size_sqm IS NULL;
UPDATE rooms SET size_sqm = 35 WHERE name = 'Standard Garden' AND size_sqm IS NULL;

-- Update room features if not set
UPDATE rooms 
SET features = 'Spacious room with a breathtaking view of the ocean, king-size bed, and private balcony.'
WHERE name = 'Deluxe Ocean View' AND features IS NULL;

UPDATE rooms 
SET features = 'Luxury suite with separate living area, premium amenities, and city skyline views.'
WHERE name = 'Executive Suite' AND features IS NULL;

UPDATE rooms 
SET features = 'Two connecting rooms with ample space for the whole family.'
WHERE name = 'Family Retreat' AND features IS NULL;

UPDATE rooms 
SET features = 'Cozy room overlooking the serene hotel gardens. Perfect for solo travelers.'
WHERE name = 'Standard Garden' AND features IS NULL;

-- Update amenities for each room type
UPDATE rooms
SET amenities = JSON_ARRAY(
    JSON_OBJECT('name', 'Free Wi-Fi', 'icon', 'fa-wifi'),
    JSON_OBJECT('name', 'Coffee Machine', 'icon', 'fa-coffee'),
    JSON_OBJECT('name', '55" Smart TV', 'icon', 'fa-tv'),
    JSON_OBJECT('name', 'Rain Shower', 'icon', 'fa-shower'),
    JSON_OBJECT('name', 'Ocean View Balcony', 'icon', 'fa-umbrella-beach'),
    JSON_OBJECT('name', 'Premium Toiletries', 'icon', 'fa-spa')
)
WHERE name = 'Deluxe Ocean View';

UPDATE rooms
SET amenities = JSON_ARRAY(
    JSON_OBJECT('name', 'Butler Service', 'icon', 'fa-user-tie'),
    JSON_OBJECT('name', 'Mini Bar', 'icon', 'fa-wine-glass-alt'),
    JSON_OBJECT('name', 'Jacuzzi', 'icon', 'fa-bath'),
    JSON_OBJECT('name', 'Walk-in Closet', 'icon', 'fa-door-open'),
    JSON_OBJECT('name', 'Marble Bathroom', 'icon', 'fa-toilet'),
    JSON_OBJECT('name', 'Living Area', 'icon', 'fa-sofa')
)
WHERE name = 'Executive Suite';

UPDATE rooms
SET amenities = JSON_ARRAY(
    JSON_OBJECT('name', 'Private Pool', 'icon', 'fa-swimming-pool'),
    JSON_OBJECT('name', 'Private Garden', 'icon', 'fa-tree'),
    JSON_OBJECT('name', 'In-Villa Dining', 'icon', 'fa-utensils'),
    JSON_OBJECT('name', 'Outdoor Rain Shower', 'icon', 'fa-shower'),
    JSON_OBJECT('name', 'Tropical Paradise', 'icon', 'fa-leaf'),
    JSON_OBJECT('name', 'Private Entrance', 'icon', 'fa-door-open')
)
WHERE name = 'Family Retreat';

UPDATE rooms
SET amenities = JSON_ARRAY(
    JSON_OBJECT('name', 'Garden View', 'icon', 'fa-leaf'),
    JSON_OBJECT('name', 'Free Wi-Fi', 'icon', 'fa-wifi'),
    JSON_OBJECT('name', 'Workspace', 'icon', 'fa-laptop'),
    JSON_OBJECT('name', 'Ensuite Bathroom', 'icon', 'fa-bath'),
    JSON_OBJECT('name', 'Air Conditioning', 'icon', 'fa-wind'),
    JSON_OBJECT('name', 'Complimentary Tea/Coffee', 'icon', 'fa-coffee')
)
WHERE name = 'Standard Garden';
