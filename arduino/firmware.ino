	/*
		Simon Says - Serial RGB LED Controller
		firmware.ino is the arduino (C) firmware for arduino + tlc5940 serial rgb led controller interface.
		sfranzyshen@facebook.com
	*/

#include "Tlc5940.h"

const int SOH = 1;  // start frame command
const int EOT = 4;  // end frame command
const int ACK = 6;  // acknowledge
const int NAK = 21; // negative acknowledge

const int RED = 0;  // rgb(i) order in the packet
const int GREEN = 1;
const int BLUE = 2;
const int INTENSITY = 3;

const int RGB =3; // red, green, blue
const int RGBI = 4; // red, green, blue, intensity ... In physics, intensity is the power transferred per unit area

const int CHANNELS = 4; // number of rgb channels
const int FRAME = CHANNELS * RGBI; // total number of channels 

const int RED_PIN = 2; // rgb positions output of tlc's
const int GREEN_PIN = 1;
const int BLUE_PIN = 0;

int inputCommand; // input command
int inputBuffer[FRAME]; // input frame buffer
int brightness = 255; // overall brightness

void setup() {
  Tlc.init();
  Serial.begin(115200);
  Serial.print("Connected"); // send connected to host
}

void loop() {
                                      // frame                 SOH CH0-R  CH0-G  CH0-B  CH0-I  CH1-R  CH1-G  CH1-B  CH1-I  CH2-R  CH2-G  CH2-B  CH2-I  CH3-R  CH3-G  CH3-B  CH3-I EOT 
                                      // layout                --- -----  -----  -----  -----  -----  -----  -----  -----  -----  -----  -----  -----  -----  -----  -----  ----- ---
  if (Serial.available() >= FRAME+2) {  //          16 bytes =  1, 0-255, 0-255, 0-255, 0-255, 0-255, 0-255, 0-255, 0-255, 0-255, 0-255, 0-255, 0-255, 0-255, 0-255, 0-255, 0-255, 4
    inputCommand = Serial.read();
    if(inputCommand == SOH) {  // start of frame command
      for(int n = 0; n < FRAME; n++) { // read frame into buffer
        inputBuffer[n] = Serial.read();
      }
      inputCommand = Serial.read();
      if(inputCommand == EOT) { // end of frame command
        Serial.print(ACK); //send host ACK
        for(int n = 0; n < CHANNELS; n++) {
          Tlc.set(RED_PIN + (n*RGB), map(inputBuffer[RED + (n*RGBI)], 0, 255, 0, map(inputBuffer[INTENSITY + (n*RGBI)], 0, 255, 0, 4095)));
          Tlc.set(GREEN_PIN + (n*RGB), map(inputBuffer[GREEN + (n*RGBI)], 0, 255, 0, map(inputBuffer[INTENSITY + (n*RGBI)], 0, 255, 0, 4095))); // set tlc channel n * 3
          Tlc.set(BLUE_PIN + (n*RGB), map(inputBuffer[BLUE + (n*RGBI)], 0, 255, 0, map(inputBuffer[INTENSITY + (n*RGBI)], 0, 255, 0, 4095))); 
        }
        Tlc.update();
      } else {
        Serial.print(NAK);
      }
    }
  } 
} // end of loop

