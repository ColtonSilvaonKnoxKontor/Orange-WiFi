# [!] By Colton Silva (chinawaterstealers).
# SiSH System Makefile

CXX = g++
CXXFLAGS = -O3 -Wall
BIN_DIR = bin
APPLETS_DIR = bin/applets

# System Paths
INSTALL_BIN = /usr/bin
INSTALL_APPLETS = /usr/libexec/applet

all: prepare sish dice masturbation bash sitop

prepare:
	mkdir -p $(BIN_DIR)
	mkdir -p $(APPLETS_DIR)

sish: sish.cpp
	$(CXX) $(CXXFLAGS) sish.cpp -o $(BIN_DIR)/sish

dice: applets/dice.cpp
	$(CXX) $(CXXFLAGS) applets/dice.cpp -o $(APPLETS_DIR)/dice

masturbation: applets/masturbation.cpp
	$(CXX) $(CXXFLAGS) applets/masturbation.cpp -o $(APPLETS_DIR)/masturbation

bash: applets/bash.cpp
	$(CXX) $(CXXFLAGS) applets/bash.cpp -o $(APPLETS_DIR)/bash

sitop: applets/sitop.cpp
	$(CXX) $(CXXFLAGS) applets/sitop.cpp -o $(APPLETS_DIR)/sitop

install: all
	mkdir -p $(INSTALL_APPLETS)
	cp $(BIN_DIR)/sish $(INSTALL_BIN)/sish
	cp $(APPLETS_DIR)/dice $(INSTALL_APPLETS)/dice
	cp $(APPLETS_DIR)/masturbation $(INSTALL_APPLETS)/masturbation
	cp $(APPLETS_DIR)/bash $(INSTALL_APPLETS)/bash
	cp $(APPLETS_DIR)/sitop $(INSTALL_APPLETS)/sitop
	chmod 755 $(INSTALL_BIN)/sish
	chmod 755 $(INSTALL_APPLETS)/dice
	chmod 755 $(INSTALL_APPLETS)/masturbation
	chmod 755 $(INSTALL_APPLETS)/bash
	chmod 755 $(INSTALL_APPLETS)/sitop
	@echo "[SUCCESS] SiSH components deployed to system paths."

clean:
	rm -rf $(BIN_DIR)

.PHONY: all prepare sish dice masturbation bash sitop install clean