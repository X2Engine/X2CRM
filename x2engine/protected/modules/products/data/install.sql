DROP TABLE IF EXISTS x2_products;
/*&*/
CREATE TABLE x2_products(
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name`       VARCHAR(255) NOT NULL,
    nameId       VARCHAR(250) DEFAULT NULL,
    `type`       VARCHAR(100),
    price        DECIMAL(18,2),
    inventory    INT,
    description  TEXT,
    createDate   BIGINT,
    lastUpdated  BIGINT,
    lastActivity BIGINT,
    updatedBy    VARCHAR(50),
    status       VARCHAR(20),
    currency     VARCHAR(40),
    adjustment   DECIMAL(18,2),
    UNIQUE(nameId)
) COLLATE = utf8_general_ci;
/*&*/
INSERT INTO `x2_modules`
(`name`, title, visible, menuPosition, searchable, editable, adminOnly, custom, toggleable)
VALUES
('products', 'Products', 1, 13, 1, 1, 0, 0, 0);
/*&*/
INSERT INTO x2_fields
(modelName, fieldName, attributeLabel, modified, custom, `type`, required, readOnly, linkType, searchable, isVirtual, relevance, uniqueConstraint, safe, keyType)
VALUES
('Product', 'currency',     'Currency',      0, 0, 'dropdown', 0, 0, '101', 0, 0, '',       0, 1, NULL),
('Product', 'status',       'Status',        0, 0, 'dropdown', 0, 0, '100', 0, 0, '',       0, 1, NULL),
('Product', 'id',           'ID',            0, 0, 'varchar',  0, 0, NULL,  0, 0, '',       1, 1, 'PRI'),
('Product', 'name',         'Name',          0, 0, 'varchar',  0, 0, NULL,  1, 0, 'High',   0, 1, NULL),
('Product', 'nameId',       'NameId',        0, 0, 'varchar',  0, 1, NULL,  1, 0, 'High',   0, 1, 'FIX'),
('Product', 'type',         'Type',          0, 0, 'varchar',  0, 0, NULL,  0, 0, '',       0, 1, NULL),
('Product', 'price',        'Price',         0, 0, 'currency', 0, 0, NULL,  0, 0, '',       0, 1, NULL),
('Product', 'inventory',    'Inventory',     0, 0, 'varchar',  0, 0, NULL,  0, 0, '',       0, 1, NULL),
('Product', 'description',  'Description',   0, 0, 'text',     0, 0, NULL,  1, 0, 'Medium', 0, 1, NULL),
('Product', 'createDate',   'Create Date',   0, 0, 'dateTime', 0, 1, NULL,  0, 0, '',       0, 1, NULL),
('Product', 'lastUpdated',  'Last Updated',  0, 0, 'dateTime', 0, 1, NULL,  0, 0, '',       0, 1, NULL),
('Product', 'lastActivity', 'Last Activity', 0, 0, 'dateTime', 0, 1, NULL,  0, 0, '',       0, 1, NULL),
('Product', 'updatedBy',    'Updated By',    0, 0, 'varchar',  0, 1, NULL,  0, 0, '',       0, 1, NULL),
('Product', 'adjustment',   'Adjustment',    0, 0, 'varchar',  0, 0, NULL,  0, 0, '',       0, 1, NULL);
