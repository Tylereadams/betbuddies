from PIL import Image
from numpy import array
from sklearn.ensemble import RandomForestClassifier
from sklearn.model_selection import train_test_split
from random import randint
import os
import sys
import urllib
import csv
import numpy

# Returns data about the file given
def getFileData(filePath):

	# Remote file, so save it temporarily
	newFilename = os.path.basename(filePath);
	urllib.urlretrieve(filePath, 'data/temp/' + newFilename)
	filePath = 'data/temp/' + newFilename

	# convert image to 8-bit grayscale
	img = Image.open(filePath).convert('L')
	WIDTH, HEIGHT = img.size
	img = img.resize([400,400],Image.ANTIALIAS)
	data = list(img.getdata()) # convert image data to a list of integers
	# convert that to 2D list (list of lists of integers)
	data = [data[offset:offset+WIDTH] for offset in range(0, WIDTH*HEIGHT, WIDTH)]

	arr = array(img)

	imgValues = []
	for innerArrays in arr:
		for val in innerArrays:
			imgValues.append(val)

	return imgValues

# Change the directory to this script's path
os.chdir(os.path.dirname(os.path.abspath(__file__)))

imageUrl = sys.argv[1]
dataPath = os.getcwd() + '/data/' + sys.argv[2]
imageToCheck = getFileData(imageUrl)
imageFileName = os.path.basename(imageUrl)
tempPath = os.getcwd() + "/data/temp/" + os.path.basename(imageUrl)
goodLabels = []
badLabels = []
goodTestValues = []
badTestValues = []

#Assuming res is a flat list
with open(dataPath + "/good_test_values.csv") as goodTestFile:
    reader = csv.reader(goodTestFile, quoting=csv.QUOTE_NONNUMERIC)
    for row in reader:
        goodTestValues.append(row)

with open(dataPath + "/bad_test_values.csv") as badTestFile:
    reader = csv.reader(badTestFile, quoting=csv.QUOTE_NONNUMERIC) # change contents to floats
    for row in reader: # each row is a list
        badTestValues.append(row)

# Setup the training data
i = 0
while i < len(goodTestValues):
	goodLabels.append(1)
	i+=1

i = 0
while i < len(badTestValues):
	badLabels.append(0)
	i+=1

features_train = array(goodTestValues + badTestValues)
labels_train = array(goodLabels + badLabels)

# initialize
clf = RandomForestClassifier()

# # train the classifier using the training data
clf.fit(features_train, labels_train)

# Make sure images have same num of values in their array
diff = abs(clf.n_features_ - len(imageToCheck))
# Make image arrays the same size to compare
if(diff > 0):
	for i in range(0, diff):
		if(len(imageToCheck) > clf.n_features_):
			imageToCheck.pop(i) # Remove the difference if image has more
		else:
			imageToCheck.append(0) # Add white (0) if image has less

# Predicting...
predicted = clf.predict([imageToCheck]) # [1]

print(predicted)
