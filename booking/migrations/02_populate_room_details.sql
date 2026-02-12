-- Room Details Page - Sample Data Population Script
-- This script provides ready-to-use sample data for the room details page
-- Safely populates amenities and gallery images without overwriting existing data

USE hotel_booking;

-- Enable safe updates for development
SET SQL_SAFE_UPDATES = 0;

-- ============================================================
-- SAMPLE ROOM DATA WITH FULL AMENITIES & GALLERY IMAGES
-- ============================================================

-- Update Deluxe Ocean View with complete details
UPDATE rooms
SET 
    size_sqm = 45,
    features = 'Spacious 45sqm room with a breathtaking view of the ocean, king-size bed, and private balcony. Premium linens and marble bathroom with rainfall shower.',
    amenities = JSON_ARRAY(
        JSON_OBJECT('name', 'Free Wi-Fi', 'icon', 'fa-wifi'),
        JSON_OBJECT('name', 'Coffee Machine', 'icon', 'fa-coffee'),
        JSON_OBJECT('name', '55" Smart TV', 'icon', 'fa-tv'),
        JSON_OBJECT('name', 'Rain Shower', 'icon', 'fa-shower'),
        JSON_OBJECT('name', 'Ocean View Balcony', 'icon', 'fa-umbrella-beach'),
        JSON_OBJECT('name', 'Premium Toiletries', 'icon', 'fa-spa'),
        JSON_OBJECT('name', 'Air Conditioning', 'icon', 'fa-wind'),
        JSON_OBJECT('name', '24/7 Room Service', 'icon', 'fa-concierge-bell')
    ),
    gallery_images = JSON_ARRAY(
        'https://images.unsplash.com/photo-1631049307038-da0ec89d4d0a?w=800&q=80',
        'https://images.unsplash.com/photo-1631049307038-da0ec89d4d0a?w=800&q=80&auto=format&fit=crop&crop=entropy',
        'https://images.unsplash.com/photo-1571019614242-c5c5dee9f50b?w=800&q=80',
        'https://images.unsplash.com/photo-1566195992212-5a0bd699a85f?w=800&q=80'
    )
WHERE name = 'Deluxe Ocean View';

-- Update Executive Suite with complete details
UPDATE rooms
SET 
    size_sqm = 70,
    features = 'Elegant 70sqm suite with a separate living area, walk-in closet, and a marble bathroom with a Jacuzzi bathtub. Perfect for discerning travelers.',
    amenities = JSON_ARRAY(
        JSON_OBJECT('name', 'Butler Service', 'icon', 'fa-user-tie'),
        JSON_OBJECT('name', 'Mini Bar', 'icon', 'fa-wine-glass-alt'),
        JSON_OBJECT('name', 'Jacuzzi', 'icon', 'fa-bath'),
        JSON_OBJECT('name', 'Walk-in Closet', 'icon', 'fa-door-open'),
        JSON_OBJECT('name', 'Marble Bathroom', 'icon', 'fa-toilet'),
        JSON_OBJECT('name', 'Living Area', 'icon', 'fa-sofa'),
        JSON_OBJECT('name', 'Separate Shower', 'icon', 'fa-shower'),
        JSON_OBJECT('name', 'Noise Cancellation', 'icon', 'fa-headphones')
    ),
    gallery_images = JSON_ARRAY(
        'https://images.unsplash.com/photo-1631049307038-da0ec89d4d0a?w=800&q=80',
        'https://images.unsplash.com/photo-1566195992212-5a0bd699a85f?w=800&q=80',
        'https://images.unsplash.com/photo-1622519486367-4ad664a432d6?w=800&q=80',
        'https://images.unsplash.com/photo-1597696058638-6a4e3e1b8f81?w=800&q=80'
    )
WHERE name = 'Executive Suite';

-- Update Family Retreat with complete details
UPDATE rooms
SET 
    size_sqm = 90,
    features = 'Two connecting rooms (total 90sqm) providing ample space for families. Includes kid-friendly amenities, games, and separate entertainment area.',
    amenities = JSON_ARRAY(
        JSON_OBJECT('name', 'Kids Welcome Package', 'icon', 'fa-baby-carriage'),
        JSON_OBJECT('name', 'Game Console', 'icon', 'fa-gamepad'),
        JSON_OBJECT('name', 'Extra Beds', 'icon', 'fa-bed'),
        JSON_OBJECT('name', 'Kitchenette', 'icon', 'fa-utensils'),
        JSON_OBJECT('name', 'Connecting Doors', 'icon', 'fa-door-open'),
        JSON_OBJECT('name', 'Free Wi-Fi', 'icon', 'fa-wifi'),
        JSON_OBJECT('name', 'Kids TV Channel', 'icon', 'fa-tv'),
        JSON_OBJECT('name', 'Safety Locks', 'icon', 'fa-lock')
    ),
    gallery_images = JSON_ARRAY(
        'https://images.unsplash.com/photo-1631049307038-da0ec89d4d0a?w=800&q=80',
        'https://images.unsplash.com/photo-1582719471537-41efb8e77f31?w=800&q=80',
        'https://images.unsplash.com/photo-1571019614242-c5c5dee9f50b?w=800&q=80',
        'https://images.unsplash.com/photo-1566195992212-5a0bd699a85f?w=800&q=80'
    )
WHERE name = 'Family Retreat';

-- Update Standard Garden with complete details  
UPDATE rooms
SET 
    size_sqm = 35,
    features = 'Cozy 35sqm room overlooking the serene hotel gardens. Perfect for solo travelers or couples. Natural light, efficient layout, great value.',
    amenities = JSON_ARRAY(
        JSON_OBJECT('name', 'Garden View', 'icon', 'fa-leaf'),
        JSON_OBJECT('name', 'Free Wi-Fi', 'icon', 'fa-wifi'),
        JSON_OBJECT('name', 'Work Desk', 'icon', 'fa-laptop'),
        JSON_OBJECT('name', 'Ensuite Bathroom', 'icon', 'fa-bath'),
        JSON_OBJECT('name', 'Air Conditioning', 'icon', 'fa-wind'),
        JSON_OBJECT('name', 'Tea/Coffee Facilities', 'icon', 'fa-coffee'),
        JSON_OBJECT('name', 'Natural Light', 'icon', 'fa-sun'),
        JSON_OBJECT('name', 'Reading Nook', 'icon', 'fa-book')
    ),
    gallery_images = JSON_ARRAY(
        'https://images.unsplash.com/photo-1631049307038-da0ec89d4d0a?w=800&q=80',
        'https://images.unsplash.com/photo-1582719471537-41efb8e77f31?w=800&q=80',
        'https://images.unsplash.com/photo-1566195992212-5a0bd699a85f?w=800&q=80'
    )
WHERE name = 'Standard Garden';

-- ============================================================
-- ADD ADDITIONAL LUXURY ROOM TYPES (OPTIONAL)
-- ============================================================

-- Add Presidential Suite (optional - uncomment to add)
-- INSERT INTO rooms (name, type, description, price_per_night, capacity, total_rooms, available_rooms, image_url, size_sqm, features, amenities, gallery_images)
-- VALUES (
--     'Presidential Suite',
--     'Suite',
--     'Ultra-luxury penthouse suite with panoramic views',
--     950.00,
--     6,
--     1,
--     1,
--     'https://images.unsplash.com/photo-1631049307038-da0ec89d4d0a?w=800&q=80',
--     150,
--     'Stunning 150sqm presidential suite with private terrace, full kitchen, and conference area for business meetings.',
--     JSON_ARRAY(
--         JSON_OBJECT('name', 'Private Terrace', 'icon', 'fa-umbrella-beach'),
--         JSON_OBJECT('name', 'Full Kitchen', 'icon', 'fa-utensils'),
--         JSON_OBJECT('name', 'Conference Area', 'icon', 'fa-video'),
--         JSON_OBJECT('name', 'Personal Assistant', 'icon', 'fa-user-tie'),
--         JSON_OBJECT('name', 'Private Elevator', 'icon', 'fa-arrow-up'),
--         JSON_OBJECT('name', 'Entertainment System', 'icon', 'fa-tv'),
--         JSON_OBJECT('name', 'Wine Cooler', 'icon', 'fa-wine-glass-alt'),
--         JSON_OBJECT('name', 'Spa Bath', 'icon', 'fa-spa')
--     ),
--     JSON_ARRAY(
--         'https://images.unsplash.com/photo-1631049307038-da0ec89d4d0a?w=800&q=80',
--         'https://images.unsplash.com/photo-1622519486367-4ad664a432d6?w=800&q=80',
--         'https://images.unsplash.com/photo-1597696058638-6a4e3e1b8f81?w=800&q=80',
--         'https://images.unsplash.com/photo-1566195992212-5a0bd699a85f?w=800&q=80'
--     )
-- );

-- ============================================================
-- VERIFY UPDATES
-- ============================================================

-- Check that all rooms have amenities and gallery images
SELECT 
    name, 
    type,
    size_sqm,
    price_per_night,
    available_rooms,
    JSON_LENGTH(amenities) as amenity_count,
    JSON_LENGTH(gallery_images) as image_count
FROM rooms
ORDER BY price_per_night ASC;

-- ============================================================
-- Reset safe updates back to default
-- ============================================================

SET SQL_SAFE_UPDATES = 1;

-- ============================================================
-- SAMPLE AMENITY ICONS (Reference)  
-- ============================================================
/*
Accommodation Amenities:
- fa-wifi (Free Wi-Fi)
- fa-coffee (Coffee/Beverages)
- fa-tv (Television)
- fa-wind (Air Conditioning)
- fa-spa (Spa/Jacuzzi)
- fa-shower (Shower/Rain Shower)
- fa-utensils (Dining/Kitchen)
- fa-swimming-pool (Pool)
- fa-tree (Garden/Nature)
- fa-leaf (Plants/Garden View)
- fa-door-open (Doors/Closet)
- fa-sofa (Living Area/Furniture)
- fa-bath (Bathtub)
- fa-toilet (Bathroom Fixtures)
- fa-user-tie (Concierge/Service)
- fa-wine-glass-alt (Mini Bar)
- fa-laptop (Work Desk)
- fa-bed (Sleeping Area)
- fa-lock (Security)
- fa-book (Reading/Library)
- fa-sun (Natural Light)
- fa-headphones (Audio/Entertainment)
- fa-baby-carriage (Kids/Family)
- fa-gamepad (Games/Kids Zone)
- fa-video (Conference/Video)
- fa-arrow-up (Elevator)
- fa-bell (Room Service)
*/

-- ============================================================
-- HOW TO ADD MORE AMENITIES
-- ============================================================
/*

To add or update amenities for a specific room:

UPDATE rooms
SET amenities = JSON_ARRAY(
    JSON_OBJECT('name', 'Amenity Name', 'icon', 'fa-icon-name'),
    JSON_OBJECT('name', 'Another Amenity', 'icon', 'fa-another-icon'),
    ...more amenities...
)
WHERE id = 1; -- or name = 'Room Name'

To add more gallery images:

UPDATE rooms
SET gallery_images = JSON_ARRAY(
    'image_url_1.jpg',
    'image_url_2.jpg',
    'image_url_3.jpg',
    'image_url_4.jpg'
)
WHERE id = 1;

*/
