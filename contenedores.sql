/*
 Navicat MySQL Data Transfer

 Source Server         : MariaDB
 Source Server Type    : MariaDB
 Source Server Version : 100207 (10.2.7-MariaDB)
 Source Host           : localhost:3306
 Source Schema         : contenedores

 Target Server Type    : MariaDB
 Target Server Version : 100207 (10.2.7-MariaDB)
 File Encoding         : 65001

 Date: 07/12/2024 10:42:48
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for camiones
-- ----------------------------
DROP TABLE IF EXISTS `camiones`;
CREATE TABLE `camiones`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `numero_economico` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `placas` varchar(15) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `conductor` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `numero_economico`(`numero_economico`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for contenedores
-- ----------------------------
DROP TABLE IF EXISTS `contenedores`;
CREATE TABLE `contenedores`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `numero_contenedor` varchar(15) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `tamano` enum('20HC','40HC') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `estado` enum('Dentro','Fuera') CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT 'Fuera',
  `fecha_ultima_modificacion` datetime NULL DEFAULT current_timestamp() ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `numero_contenedor`(`numero_contenedor`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 8 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for registros
-- ----------------------------
DROP TABLE IF EXISTS `registros`;
CREATE TABLE `registros`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contenedor_id` int(11) NOT NULL,
  `camion_id` int(11) NOT NULL,
  `flujo` enum('Entrada','Salida') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `fecha_hora` datetime NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `contenedor_id`(`contenedor_id`) USING BTREE,
  INDEX `camion_id`(`camion_id`) USING BTREE,
  CONSTRAINT `registros_ibfk_1` FOREIGN KEY (`contenedor_id`) REFERENCES `contenedores` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `registros_ibfk_2` FOREIGN KEY (`camion_id`) REFERENCES `camiones` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 13 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- View structure for inventarioactual
-- ----------------------------
DROP VIEW IF EXISTS `inventarioactual`;
CREATE ALGORITHM = UNDEFINED SQL SECURITY DEFINER VIEW `inventarioactual` AS select `c`.`numero_contenedor` AS `numero_contenedor`,`c`.`tamano` AS `tamano`,`c`.`estado` AS `estado`,`r`.`flujo` AS `flujo`,`r`.`fecha_hora` AS `fecha_hora` from (`contenedores` `c` join `registros` `r` on(`c`.`id` = `r`.`contenedor_id`)) where `c`.`estado` = 'Dentro';

-- ----------------------------
-- Procedure structure for ActualizarEntrada
-- ----------------------------
DROP PROCEDURE IF EXISTS `ActualizarEntrada`;
delimiter ;;
CREATE PROCEDURE `ActualizarEntrada`(IN p_id INT,
    IN p_numero_contenedor VARCHAR(50),
    IN p_tamano_contenedor VARCHAR(10),
    IN p_numero_economico VARCHAR(50),
    IN p_numero_placa VARCHAR(50),
    IN p_fecha_hora DATETIME)
BEGIN
    -- Verificar si el contenedor existe
    IF NOT EXISTS (SELECT 1 FROM contenedores WHERE id = p_id) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El contenedor especificado no existe.';
    END IF;

    -- Actualizar los datos del contenedor
    UPDATE contenedores
    SET numero_contenedor = p_numero_contenedor, 
        tamano = p_tamano_contenedor
    WHERE id = p_id;

    -- Actualizar los datos del camión relacionado
    UPDATE camiones
    SET numero_economico = p_numero_economico,
        placas = p_numero_placa
    WHERE id = (SELECT camion_id FROM registros WHERE contenedor_id = p_id);

    -- Actualizar la hora de entrada en los registros
    UPDATE registros
    SET fecha_hora = p_fecha_hora
    WHERE contenedor_id = p_id AND flujo = 'Entrada';
END
;;
delimiter ;

-- ----------------------------
-- Procedure structure for RegistrarEntrada
-- ----------------------------
DROP PROCEDURE IF EXISTS `RegistrarEntrada`;
delimiter ;;
CREATE PROCEDURE `RegistrarEntrada`(IN p_numero_contenedor VARCHAR(15),
    IN p_tamano ENUM('20HC', '40HC'),
    IN p_numero_economico VARCHAR(10),
    IN p_placas VARCHAR(15),
    IN p_conductor VARCHAR(100))
BEGIN
    DECLARE v_contenedor_id INT;
    DECLARE v_camion_id INT;
    DECLARE v_contenedores_dentro INT;
    DECLARE v_registro_id INT; -- Aquí se declara la variable v_registro_id

    -- Verificar si el contenedor ya está en el almacén
    SELECT id INTO v_contenedor_id
    FROM contenedores
    WHERE numero_contenedor = p_numero_contenedor AND estado = 'Dentro';
    
    -- Si el contenedor ya está dentro, no insertarlo de nuevo, solo actualizar el estado
    IF v_contenedor_id IS NOT NULL THEN
        -- Actualizamos el estado si ya está dentro
        UPDATE contenedores
        SET estado = 'Dentro', fecha_ultima_modificacion = NOW()
        WHERE id = v_contenedor_id;
    ELSE
        -- Si no existe el contenedor, se inserta
        SELECT id INTO v_contenedor_id
        FROM contenedores
        WHERE numero_contenedor = p_numero_contenedor;

        IF v_contenedor_id IS NULL THEN
            INSERT INTO contenedores (numero_contenedor, tamano, estado)
            VALUES (p_numero_contenedor, p_tamano, 'Dentro');
            SET v_contenedor_id = LAST_INSERT_ID();
        END IF;
    END IF;

    -- Validar la combinación de contenedores permitidos para el camión al entrar al almacén
    SELECT COUNT(c.id) INTO v_contenedores_dentro
    FROM contenedores c
    JOIN registros r ON c.id = r.contenedor_id
    WHERE r.camion_id = v_camion_id AND c.estado = 'Dentro' AND c.tamano = '20HC';

    IF p_tamano = '40HC' AND v_contenedores_dentro > 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Un camión con un contenedor 40HC no puede llevar otros contenedores.';
    END IF;

    IF p_tamano = '20HC' AND v_contenedores_dentro >= 2 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Un camión no puede llevar más de 2 contenedores 20HC.';
    END IF;

    -- Insertar o verificar si existe el camión
    SELECT id INTO v_camion_id
    FROM camiones
    WHERE numero_economico = p_numero_economico;
    
    -- Si no existe el camión, se inserta
    IF v_camion_id IS NULL THEN
        INSERT INTO camiones (numero_economico, placas, conductor)
        VALUES (p_numero_economico, p_placas, p_conductor);
        SET v_camion_id = LAST_INSERT_ID();
    ELSE
        -- Si ya existe, actualizamos el camión
        UPDATE camiones
        SET placas = p_placas, conductor = p_conductor
        WHERE id = v_camion_id;
    END IF;

    -- Insertar o actualizar el registro de entrada
    SELECT id INTO v_registro_id
    FROM registros
    WHERE contenedor_id = v_contenedor_id AND camion_id = v_camion_id AND flujo = 'Entrada';
    
    IF v_registro_id IS NULL THEN
        -- Si no existe el registro de entrada, insertamos uno nuevo
        INSERT INTO registros (contenedor_id, camion_id, flujo, fecha_hora)
        VALUES (v_contenedor_id, v_camion_id, 'Entrada', NOW());
    ELSE
        -- Si el registro ya existe, actualizamos la fecha_hora
        UPDATE registros
        SET fecha_hora = NOW()
        WHERE id = v_registro_id;
    END IF;

END
;;
delimiter ;

-- ----------------------------
-- Procedure structure for RegistrarSalida
-- ----------------------------
DROP PROCEDURE IF EXISTS `RegistrarSalida`;
delimiter ;;
CREATE PROCEDURE `RegistrarSalida`(IN p_numero_contenedor VARCHAR(15),
    IN p_numero_economico VARCHAR(10))
BEGIN
    DECLARE v_contenedor_id INT;
    DECLARE v_camion_id INT;
    DECLARE v_tamano ENUM('20HC', '40HC');
    DECLARE v_contenedores_camion INT;

    -- Verificar que el contenedor esté en el almacén
    SELECT id, tamano INTO v_contenedor_id, v_tamano
    FROM contenedores
    WHERE numero_contenedor = p_numero_contenedor AND estado = 'Dentro';

    IF v_contenedor_id IS NULL THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El contenedor no está en el almacén.';
    END IF;

    -- Verificar que el camión esté registrado
    SELECT id INTO v_camion_id
    FROM camiones
    WHERE numero_economico = p_numero_economico;

    IF v_camion_id IS NULL THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El camión no está registrado.';
    END IF;

    -- Validar la cantidad de contenedores permitidos por camión solo si el contenedor es 20HC
    IF v_tamano = '20HC' THEN
        SELECT COUNT(c.id) INTO v_contenedores_camion
        FROM registros r
        JOIN contenedores c ON r.contenedor_id = c.id
        WHERE r.camion_id = v_camion_id AND r.flujo = 'Salida' AND c.estado = 'Fuera'
          AND c.tamano = '20HC';

        IF v_contenedores_camion >= 2 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Un camión no puede llevar más de 2 contenedores 20HC.';
        END IF;
    END IF;

    -- Si es 40HC, no realizamos ninguna validación adicional sobre el número de contenedores en el camión
    -- (deshabilitamos la validación para el contenedor 40HC)

    -- Actualizar el estado del contenedor a "Fuera"
    UPDATE contenedores
    SET estado = 'Fuera'
    WHERE id = v_contenedor_id;

    -- Insertar el registro de salida
    INSERT INTO registros (contenedor_id, camion_id, flujo)
    VALUES (v_contenedor_id, v_camion_id, 'Salida');
END
;;
delimiter ;

SET FOREIGN_KEY_CHECKS = 1;
