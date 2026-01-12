-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 13/01/2026 às 00:37
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `marcar_consulta`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `agendamentos`
--

CREATE TABLE `agendamentos` (
  `id` int(11) NOT NULL,
  `nome_paciente` varchar(100) NOT NULL,
  `telefone` varchar(20) NOT NULL,
  `profissional_id` int(11) NOT NULL,
  `data_consulta` date NOT NULL,
  `horario_consulta` time NOT NULL,
  `tipo_consulta` enum('Presencial','Online') NOT NULL,
  `status` enum('Agendada','Já realizada') DEFAULT 'Agendada',
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `agendamentos`
--

INSERT INTO `agendamentos` (`id`, `nome_paciente`, `telefone`, `profissional_id`, `data_consulta`, `horario_consulta`, `tipo_consulta`, `status`, `data_criacao`) VALUES
(1, 'Samuel Boaz', '(11) 11111-1111', 1, '2026-01-09', '09:00:00', 'Presencial', 'Já realizada', '2026-01-08 19:36:12'),
(2, 'Samuel Boaz', '(12) 22222-2244', 1, '2026-01-15', '16:30:00', 'Presencial', 'Agendada', '2026-01-12 18:57:50'),
(3, 'Julia Kalika', '(23) 32323-2323', 2, '2026-01-18', '16:30:00', 'Online', 'Agendada', '2026-01-12 18:58:38'),
(4, 'Estela Formiga', '(77) 77777-7777', 2, '2026-01-16', '10:30:00', 'Presencial', 'Agendada', '2026-01-12 19:05:40'),
(6, 'marcos julio', '(12) 22222-2222', 1, '2026-01-20', '09:00:00', 'Online', 'Agendada', '2026-01-12 19:29:57'),
(7, 'Kalvin Marco', '(48) 95239-8457', 2, '2026-01-15', '11:00:00', 'Online', 'Agendada', '2026-01-12 19:31:55');

-- --------------------------------------------------------

--
-- Estrutura para tabela `profissionais`
--

CREATE TABLE `profissionais` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `senha` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `profissionais`
--

INSERT INTO `profissionais` (`id`, `nome`, `usuario`, `senha`) VALUES
(1, 'Dr. João Silva', 'joao_silva', '0192023a7bbd73250516f069df18b500'),
(2, 'Dra. Maria Oliveira', 'maria_oliveira', '0192023a7bbd73250516f069df18b500');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `agendamentos`
--
ALTER TABLE `agendamentos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_agenda` (`data_consulta`,`horario_consulta`,`profissional_id`),
  ADD KEY `profissional_id` (`profissional_id`);

--
-- Índices de tabela `profissionais`
--
ALTER TABLE `profissionais`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `agendamentos`
--
ALTER TABLE `agendamentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `profissionais`
--
ALTER TABLE `profissionais`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `agendamentos`
--
ALTER TABLE `agendamentos`
  ADD CONSTRAINT `agendamentos_ibfk_1` FOREIGN KEY (`profissional_id`) REFERENCES `profissionais` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;


version: '3.8'

services:
  # Servidor de Banco de Dados
  db:
    image: mysql:5.7
    container_name: mysql_container
    restart: always
    environment:
      # Defina aqui o nome do banco e a senha (os mesmos que usará no db.php)
      MYSQL_DATABASE: marcar_consulta
      MYSQL_ROOT_PASSWORD: minha senha_segura
    volumes:
      # O Docker executa automaticamente qualquer .sql dentro desta pasta na primeira vez
      - ./sql:/docker-entrypoint-initdb.d
    ports:
      - "3306:3306"

  # Servidor Web (PHP + Apache)
  web:
    image: php:8.0-apache
    container_name: php_container
    restart: always
    ports:
      - "80:80"
    volumes:
      # Mapeia a raiz do seu projeto para a pasta do servidor web
      - ./:/var/www/html
    depends_on:
      - db




      CREATE DATABASE IF NOT EXISTS marcar_consulta CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE marcar_consulta;

CREATE TABLE profissionais (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL
);

CREATE TABLE agendamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome_paciente VARCHAR(100) NOT NULL,
    telefone VARCHAR(20) NOT NULL,
    profissional_id INT NOT NULL,
    data_consulta DATE NOT NULL,
    horario_consulta TIME NOT NULL,
    tipo_consulta ENUM('Presencial', 'Online') NOT NULL DEFAULT 'Presencial',
    status ENUM('Agendada', 'Já realizada') NOT NULL DEFAULT 'Agendada',
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (profissional_id) REFERENCES profissionais(id),
    UNIQUE KEY unique_agenda (data_consulta, horario_consulta, profissional_id)
);

INSERT INTO profissionais (nome, usuario, senha) VALUES 
('Dr. João Silva', 'joao_silva', '0192023a7bbd73250516f069df18b500'),
('Dra. Maria Oliveira', 'maria_oliveira', '0192023a7bbd73250516f069df18b500');