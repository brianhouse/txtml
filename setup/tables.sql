drop table if exists default_state;
create table default_state (
state varchar (255)
) engine=MyISAM;

drop table if exists sms;
create table sms (
id int auto_increment,
address varchar (255),
send bool,
time datetime,
content text,
raw text,
state varchar(255),
user_id int,
primary key (id),
key (send),
key (state),
key (user_id)
) engine=MyISAM;

drop table if exists states;
create table states (
id int auto_increment,
name varchar (255) unique,
start bool,
txtml text,
filename varchar (255),
primary key (id),
key (name)
) engine=MyISAM;

drop table if exists blocks;
create table blocks (
id int auto_increment,
name varchar (255) unique,
txtml text,
filename varchar (255),
primary key (id),
key (name)
) engine=MyISAM;

drop table if exists router;
create table router (
id int auto_increment,
keyword varchar (255) unique,
state varchar (255),
primary key (id),
key (keyword)
) engine=MyISAM;

drop table if exists users;
create table users (
id int auto_increment,
name varchar(255),
active bool,
start datetime,
last datetime,
timeout datetime,
trig bool,
primary key (id),
key (name),
key (active),
key (timeout),
key (trig)
) engine=MyISAM;

drop table if exists feeds;
create table feeds (
id int auto_increment,
name varchar(255) unique,
active bool,
primary key (id),
key (name),
key (active)
) engine=MyISAM;

drop table if exists path;
create table path (
id int auto_increment,
user_id int,
state varchar (255),
time datetime,
primary key (id),
key (user_id)
) engine=MyISAM;

drop table if exists user_vars;
create table user_vars (
id int auto_increment,
user_id int,
var varchar(64),
value text,
primary key (id),
key (user_id),
key (var)
) engine=MyISAM;

drop table if exists feed_vars;
create table feed_vars (
id int auto_increment,
feed_id int,
var varchar(64),
value text,
primary key (id),
key (feed_id)
) engine=MyISAM;

INSERT INTO default_state VALUES ("empty");