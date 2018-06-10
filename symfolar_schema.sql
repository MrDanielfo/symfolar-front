/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
/**
 * Author:  josue
 * Created: 03-jun-2018
 */

DROP DATABASE IF EXISTS symfolar_front;

CREATE DATABASE IF NOT EXISTS symfolar_front;

USE symfolar_front; 

CREATE TABLE users(
    id          int(255) AUTO_INCREMENT NOT NULL,
    role        varchar(20),
    name        varchar(60),
    surname     varchar(60),
    email       varchar(100),
    password    varchar(255),
    created_at  datetime,
    CONSTRAINT pk_users PRIMARY KEY (id)
)ENGINE=InnoDb;


CREATE TABLE tasks(
    id          int(255) AUTO_INCREMENT NOT NULL,
    user_id     int(255) NOT NULL,
    title       varchar(255),
    description text,
    status      varchar(100),
    created_at  datetime,
    updated_at  datetime,
    CONSTRAINT pk_tasks PRIMARY KEY (id),
    CONSTRAINT fk_tasks_users FOREIGN KEY (user_id) REFERENCES users(id)
)ENGINE=InnoDb;

