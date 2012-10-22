CREATE TABLE Users (
  Uid          INT UNSIGNED     PRIMARY KEY AUTO_INCREMENT,
  UserName     VARCHAR(64)      NOT NULL UNIQUE,
  Email        VARCHAR(128)     NOT NULL UNIQUE,
  Salt         CHAR(40)         NOT NULL,
  PasswordHash CHAR(40)         NOT NULL,
  FirstName    VARCHAR(64)      NOT NULL,
  LastName     VARCHAR(64)      NOT NULL,
  Phone        CHAR(10)         NOT NULL,
  Address      VARCHAR(128)             ,
  City         VARCHAR(64)              ,
  State        CHAR(2)
);


CREATE TABLE IPSessions (
  IP           INT UNSIGNED     NOT NULL PRIMARY KEY,
  GenTime      DATETIME         NOT NULL,
  LastRequest  DATETIME         NOT NULL,
  Logins       TINYINT UNSIGNED NOT NULL
);

CREATE TABLE Sessions (
  Sid          CHAR(40)         NOT NULL PRIMARY KEY,
  GenTime      DATETIME         NOT NULL,
  Uid          INT   UNSIGNED   NOT NULL UNIQUE, /* only one session per user*/
  LastRequest  DATETIME         NOT NULL,
  IP           INT              NOT NULL
);

/* Note clean up this table every once in a while to remove duplicate Mac/Uid combos */
CREATE TABLE Macs (
  Mac          BINARY(6)        NOT NULL,
  Uid          INT UNSIGNED     NOT NULL,
  DeviceName   VARCHAR(64)      NOT NULL,
  Created      DATETIME         NOT NULL,
  Deactivated  DATETIME
);




/* Create Initial Admin Group */
INSERT INTO Users VALUES (
  /*uid         */  0,
  /*UserName    */ 'marler8997',
  /*email       */ 'marler@capp.com',
  /*salt        */ '2aacfbac8a63b589badf3b4fb2539c1ba3e28228',
  /*passwordhash*/ '7eae35d3ac68603098451b236f642eb7c8b2cf8a', /* password is 'password' */
  /*fname       */ 'Jonathan',
  /*lname       */ 'Marler',
  /*phone       */ '5093961137',
  /*addr        */ '2263 E Sadie Dr',
  /*city        */ 'Eagle',
  /*state       */ 'ID'
);
